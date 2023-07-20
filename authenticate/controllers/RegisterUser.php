<?php
namespace authenticate\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use authenticate\models\Db\UserAuthenticationDao;

use swpg\models\db;
use swpg\models\utility;
use user_account\models\UserAccountDao;
use \GUMP;
use core\models\Google\Recaptcha\RecaptchaService;

class RegisterUser {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    function __invoke(Request $request, Response $response, $next) {
        global $final_global_template_vars;

        $post_data = $request->getParams();

        if (empty($post_data["email"])) {
            $request = $request->withAttribute("errors", [
                "No email provided."
            ]);

            return $next($request, $response);
        }
        //@@@CAP-108 add confirm password field
        if ($post_data["password"] !== $post_data["confirm_password"]) {
          $request = $request->withAttribute("errors", [
              "Passwords do not match."
          ]);

          return $next($request, $response);
        }
        if (empty($post_data["password"])) {
            $request = $request->withAttribute("errors", [
                "No password provided."
            ]);

            return $next($request, $response);
        }

/*        if (empty($post_data["g-recaptcha-response"])) {
            $request = $request->withAttribute("errors", [
                "No reCAPTCHA response provided."
            ]);

            return $next($request, $response);
        }*/

/*        $recaptcha_verifier_result = (new RecaptchaService())->verifyResponse($post_data["g-recaptcha-response"]);

        if (!$recaptcha_verifier_result["success"]) {
            if (($recaptcha_verifier_result["status_code"] !== 0) && ($recaptcha_verifier_result["status_code"] !== 200)) {
                error_log("User registration (email=" . $post_data["email"] . ") reCAPTCHA validation API cURL query failed: code=" .
                    $recaptcha_verifier_result["status_code"]);
            } else {
                error_log("User registration (email=" . $post_data["email"] . ") reCAPTCHA validation API error(s): " .
                    implode("; ", $recaptcha_verifier_result["error_msgs"]));
            }

            $request = $request->withAttribute("errors", [
                "Incorrect reCAPTCHA response."
            ]);
            return $next($request, $response);
        }*/

        $db = (new db($final_global_template_vars["db_connection"]))->get_resource();
        $user_account_dao = new UserAccountDao($db, $final_global_template_vars["session_key"]);
        $user_auth_dao = new UserAuthenticationDao($db, (new db($final_global_template_vars["core_framework_db"]))->get_resource(), $user_account_dao);
        $user = $user_auth_dao->get_local_user($post_data["email"]);

        if (!empty($user)) {
            $request = $request->withAttribute("errors", [
                "This email: " . $post_data["email"] . " has been registed."
            ]);
            return $next($request, $response);
        }

        $password_reset_selector = bin2hex(random_bytes(32));
        $password_reset_token = random_bytes(64);

        $account_id = $user_auth_dao->insert_new_local_user($post_data["email"], $post_data["password"], $password_reset_selector, bin2hex(hash("sha256", $password_reset_token, true)));

        $this->submit_registration_send_mail($post_data["email"], $password_reset_selector, bin2hex($password_reset_token));

        $request = $request->withAttribute("registration_submitted", true);

        return $next($request, $response);
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    function submit_registration_send_mail(string $user_email_address, string $password_reset_selector, string $password_reset_token) {
        global $final_global_template_vars;

        $site_url_server_host = $_SERVER["SERVER_NAME"];

        if (((int)$_SERVER["SERVER_PORT"]) !== 443) {
            $site_url_server_host .= ":" . $_SERVER["SERVER_PORT"];
        }
        //@@@CAP-50 - user account and password recovery updates
        $mailer = new \core\models\Mail\Mailer($this->container->get('view'),
            $final_global_template_vars["swpg_module_list"]["authenticate"]["absolute_path_to_this_module"] . "/templates",
            $final_global_template_vars['mail_config']);

        $this->container->get('logger')->info("User email: " . $user_email_address . " Sent from: " . $final_global_template_vars["superadmin_email_address"]);

        $mailer_result = $mailer->sendSmtpMail("registration_mail.twig",
            $final_global_template_vars["superadmin_email_address"],
            $user_email_address,
            $final_global_template_vars["superadmin_email_address"],
            ($final_global_template_vars["site_name"] . ": Registration"),
            [],
            [
                "admin_email_address" => $final_global_template_vars["superadmin_email_address"],
                "confirmation_expiration_interval_formatted" => (((int)($final_global_template_vars["password_reset_expiration_interval"] / (60 * 60 * 24))) .
                    " days"),
                "confirmation_url" => "https://{$site_url_server_host}/authenticate/register/confirm/?register_selector={$password_reset_selector}&register_token={$password_reset_token}",
                "site_url" => "https://{$site_url_server_host}/"
            ]
        );

        $this->container->get('logger')->info("Mailer result: " . $mailer_result);

        if (!$mailer_result == 'Sent') {
            throw new \PHPMailer\PHPMailer\Exception($mailer_result);
        }

    }

}
