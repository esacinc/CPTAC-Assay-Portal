<?php
namespace authenticate\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use swpg\models\db;
use user_account\models\UserAccountDao;
use core\models\Google\Recaptcha\RecaptchaService;
use authenticate\models\Db\UserAuthenticationDao;


class PasswordRecoveryForm extends Controller {

    function submit_password_recovery(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $post_data = $request->getParams();

        if (empty($post_data["username"])) {
            $request = $request->withAttribute("errors", [
                    "No username provided."
            ]);

            $this->show_password_recovery_form($request, $response, $args);

            return;
        }

/*        if (empty($post_data["g-recaptcha-response"])) {
            $request = $request->withAttribute("errors", [
                    "No reCAPTCHA response provided."
            ]);
            $this->show_password_recovery_form($request, $response, $args);

            return;
        }

        $recaptcha_verifier_result = (new RecaptchaService())->verifyResponse($post_data["g-recaptcha-response"]);

        if (!$recaptcha_verifier_result["success"]) {
            if (($recaptcha_verifier_result["status_code"] !== 0) && ($recaptcha_verifier_result["status_code"] !== 200)) {
                error_log("Password recovery (username=" . $post_data["username"] . ") reCAPTCHA validation API cURL query failed: code=" .
                    $recaptcha_verifier_result["status_code"]);
            } else {
                error_log("Password recovery (username=" . $post_data["username"] . ") reCAPTCHA validation API error(s): " .
                    implode("; ", $recaptcha_verifier_result["error_msgs"]));
            }

            $request = $request->withAttribute("errors", [
                    "Incorrect reCAPTCHA response."
            ]);
            $this->show_password_recovery_form($request, $response, $args);

            return;
        }*/

        $db = (new db($final_global_template_vars["db_connection"]))->get_resource();
        $user_account_dao = new UserAccountDao($db, $final_global_template_vars["session_key"]);
        $user_auth_dao = new UserAuthenticationDao($db, (new db($final_global_template_vars["core_framework_db"]))->get_resource(), $user_account_dao);
        $user = $user_auth_dao->get_local_user($post_data["username"]);

        if (empty($user)) {
            $request = $request->withAttribute("errors", [
                    "User not found."
            ]);
            $this->show_password_recovery_form($request, $response, $args);

            return;
        }

        if (empty($user["email"])) {
            $request = $request->withAttribute("errors", [
                    "User does not have an associated email address."

            ]);
            $this->show_password_recovery_form($request, $response, $args);

            return;
        }

        if (!$user_account_dao->send_mail_preference($user)) {
            $request = $request->withAttribute("errors", [
                    "User configuration does not allow sending emails."

            ]);
            $this->show_password_recovery_form($request, $response, $args);

            return;
        }

        $password_reset_selector = bin2hex(random_bytes(32));
        $password_reset_token = random_bytes(64);

        $user_auth_dao->update_local_user_password_reset_data($user["account_id"], $password_reset_selector, bin2hex(hash("sha256", $password_reset_token, true)));

        $this->submit_password_recovery_send_mail($user["email"], $password_reset_selector, bin2hex($password_reset_token));

        $request = $request->withAttribute("password_recovery_submitted", true);

        $this->show_password_recovery_form($request, $response, $args);
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    function submit_password_recovery_send_mail(string $user_email_address, string $password_reset_selector, string $password_reset_token) {
        global $final_global_template_vars;

        $site_url_server_host = $_SERVER["SERVER_NAME"];

        if (((int)$_SERVER["SERVER_PORT"]) !== 443) {
            $site_url_server_host .= ":" . $_SERVER["SERVER_PORT"];
        }
        //@@@CAP-50 - fix user registration and password recovery
        $mailer = new \core\models\Mail\Mailer($this->container->get('view'),
            $final_global_template_vars["swpg_module_list"]["authenticate"]["absolute_path_to_this_module"] . "/templates",$final_global_template_vars["mail_config"]);

        //@@@CAP-113 - fix issue with links in password_recovery email
        $mailer->sendSmtpMail("password_recovery_mail.twig",
            $final_global_template_vars["superadmin_email_address"],
            $user_email_address,
            $final_global_template_vars["superadmin_email_address"],
            ($final_global_template_vars["site_name"] . ": Password Recovery"),
            [],
            [
                "admin_email_address" => $final_global_template_vars["superadmin_email_address"],
                "password_reset_expiration_interval_formatted" => (((int)($final_global_template_vars["password_reset_expiration_interval"] / (60 * 60 * 24))) .
                    " days"),
                "password_reset_url" => "https://{$site_url_server_host}/authenticate/password_recovery/confirm/?password_reset_selector={$password_reset_selector}&password_reset_token={$password_reset_token}",
                "site_url" => "https://{$site_url_server_host}/"
            ]
        );
    }

    function show_password_recovery_form(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $errors = $request->getAttribute("errors");
        $password_recovery_submitted = $request->getAttribute("password_recovery_submitted");


        $this->container->get('view')->render($response, "password_recovery_form.twig", [
            "errors" => !empty($errors) ? $errors : false,
            "google_recaptcha_site_key" => $final_global_template_vars["google_recaptcha_site_key"],
            "page_title" => "Password Recovery",
            "password_recovery_submitted" => isset($password_recovery_submitted) ? $password_recovery_submitted : false
        ]);
    }
}
