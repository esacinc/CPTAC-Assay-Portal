<?php
namespace authenticate\controllers;

use core\controllers\Controller;
use Slim\Http\Request;
use Slim\Http\Response;

use authenticate\models\AuthenticationFailedException;
use authenticate\models\Db\UserAuthenticationDao;
use authenticate\models\Google\GoogleUserAuthenticationService;
use core\models\Db\SqlUtils;

use swpg\models\db;
use swpg\models\utility;
use user_account\models\UserAccountDao;

class OAuth extends Controller {
    
    function oauth(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $redirect_url = $final_global_template_vars["landing_page"];
        $user_auth_service = new GoogleUserAuthenticationService();

        $new_user = empty($args['register']) ? false : true;
        $user_auth_service->setNewUser($new_user);

        try {
            if ($user_auth_service->hasToken()) {
                $user_auth_service->processToken();

                $redirect_url = $this->process_user_details($redirect_url, $user_auth_service->buildUserDetails(), $new_user);
            } else {
                $error = $request->getParam("error");

                if ($error !== null) {
                    $error_desc = $request->getParam("error_description", $error);

                    throw new AuthenticationFailedException("Unable to exchange code for access token: {$error_desc}");
                }

                $code = $request->getParam("code");

                if ($code !== null) {
                    $user_auth_service->processCode($code, $request->getParam("state"));

                    $redirect_url = $this->process_user_details($redirect_url, $user_auth_service->buildUserDetails(), $new_user);
                } else {
                    return $response->withRedirect($user_auth_service->createAuthorizationUrl($new_user));
                }
            }

            if (isset($_COOKIE[$final_global_template_vars["redirect_cookie_key"]]) &&
                ($redirect_cookie_url = $redirect_cookie_url = $_COOKIE[$final_global_template_vars["redirect_cookie_key"]])) {
                $redirect_url = $redirect_cookie_url;

                setcookie($final_global_template_vars["redirect_cookie_key"], "", (time() - 3600), "/");

                unset($_COOKIE[$final_global_template_vars["redirect_cookie_key"]]);
            }

            $this->render_oauth($response, [
                "redirect_url" => $redirect_url
            ]);
        } catch (Stop $e) {
            throw $e;
        } catch (Exception $e) {
            //$user_auth_service->getLogger()->error("Unable to authenticate via Google OAuth: {$e}");

            $this->render_oauth($response, [
                "redirect_url" => $final_global_template_vars["login_url"]
            ]);
        }
    }

    function process_user_details(string $redirect_url, array &$user_details, $new_user) {
        global $final_global_template_vars;

        foreach ($user_details as $user_details_key => $user_details_value) {
            if ($user_details_key === "given_name") {
                $user_details_key = "givenname";
            } else if ($user_details_key === "username") {
                $user_details_key = "cn";
            }

            $_SESSION[$final_global_template_vars["session_key"]][$user_details_key] = $user_details_value;
        }

        $user_account_db = (new db($final_global_template_vars["user_account_db_connection"]))->get_resource();
        $user_account_dao = new UserAccountDao($user_account_db, $final_global_template_vars["session_key"]);
        $user_auth_dao = new UserAuthenticationDao($user_account_db, (new db($final_global_template_vars["core_framework_db"]))->get_resource(), $user_account_dao);
        $user = $user_auth_dao->get_google_user($user_details["email"]);

        if (empty($user) && $new_user) {
            $user = $user_auth_dao->insert_google_user($user_details);
            $account_id = ($user["account_id"] ? $user["account_id"] : null);

            if ($account_id !== null) {
                $_SESSION[$final_global_template_vars["session_key"]]["account_id"] = $account_id;



                $user_auth_dao->log_login_attempt(true, $user_details["email"], $account_id);

                //return $final_global_template_vars["landing_page"];
            } else {
                $user_auth_dao->log_login_attempt(false, $user_details["email"], $account_id);

                return $final_global_template_vars["logout_url"];
            }
        }

        $account_id = $user["account_id"];
        $update_user = false;

        foreach ($user_details as $user_details_key => $user_details_value) {
            if (!isset($user[$user_details_key]) || ($user[$user_details_key] !== $user_details_value)) {
                $update_user = true;

                break;
            }
        }

        if ($update_user) {
            $user_account_dao->update_account($account_id, $user_details, SqlUtils::extractFields($user_details));
        }

        $_SESSION[$final_global_template_vars["session_key"]]["account_id"] = $account_id;

        $user_auth_dao->log_login_attempt(true, $user_details["email"], $account_id);

        $_SESSION[$final_global_template_vars["session_key"]][$final_global_template_vars["current_user_roles_session_key"]] =
            utility::array_flatten($user_account_dao->get_user_roles_list($account_id));

        $associated_user_groups = [];

        $_SESSION[$final_global_template_vars["session_key"]]["associated_groups"] =
            utility::array_flatten($user_account_dao->get_user_account_groups($account_id), $associated_user_groups, "group_id");

        $associated_user_groups = [];

        $_SESSION[$final_global_template_vars["session_key"]]["associated_groups_with_subgroups"] =
            utility::array_flatten($user_account_dao->get_user_account_groups_with_subgroups($account_id), $associated_user_groups, "group_id");

        return $redirect_url;
    }

    function render_oauth($response, array $data) {
        $replacement_url = "https://{$_SERVER["SERVER_NAME"]}";

        if (((int)$_SERVER["SERVER_PORT"]) !== 443) {
            $replacement_url .= ":{$_SERVER["SERVER_PORT"]}";
        }

        $replacement_url .= "/authenticate/oauth/";

        $this->container->get('view')->render($response, "oauth.twig", array_merge([
            "page_title" => "Google OAuth Login",
            "replacement_url" => $replacement_url
        ], $data));
    }
}