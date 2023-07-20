<?php

namespace authenticate\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;
use swpg\models\db;
use user_account\models\UserAccountDao;
use authenticate\models\Db\UserAuthenticationDao;

use \GUMP;

class PasswordRecoveryConfirmForm extends Controller {

    function confirm_password_recovery(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $password_reset_selector = $request->getParam("password_reset_selector");
        $password_reset_token = $request->getParam("password_reset_token");

        if (empty($password_reset_selector)) {
            $request = $request->withAttribute("errors", [
                    "No password reset selector provided."
                ]
            );

            $this->show_password_recovery_confirm_form($request, $response, $args);

            return;
        } else if (empty($password_reset_token)) {
            $request = $request->withAttribute("errors", [
                    "No password reset token provided."
                ]
            );

            $this->show_password_recovery_confirm_form($request, $response, $args);

            return;
        }

        $db = (new db($final_global_template_vars["db_connection"]))->get_resource();
        $user_account_dao = new UserAccountDao($db, $final_global_template_vars["session_key"]);
        $user_auth_dao = new UserAuthenticationDao($db, (new db($final_global_template_vars["core_framework_db"]))->get_resource(), $user_account_dao);
        $user = $user_auth_dao->get_local_user_by_password_reset_data($password_reset_selector, $final_global_template_vars["password_reset_expiration_interval"]);
        //@@@CAP-50 - fix user registration and password recovery
        if (empty($user) || !hash_equals(hex2bin($user["password_reset_token"]), hash("sha256", hex2bin($password_reset_token), true))) {

            $request = $request->withAttribute("errors", [
                    "Invalid password recovery."
                ]
            );

            $this->show_password_recovery_confirm_form($request, $response, $args);

            return;
        }

        $password = $request->getParam("password");
        $password_confirm = $request->getParam("password_confirm");

        if (empty($password)) {
            $request = $request->withAttribute("errors", [
                    "No new password provided."
                ]
            );
            $this->show_password_recovery_confirm_form($request, $response, $args);

            return;
        }

        if (empty($password_confirm)) {
            $request = $request->withAttribute("errors", [
                    "No new password confirmation provided."
                ]
            );
            $this->show_password_recovery_confirm_form($request, $response, $args);

            return;
        }

        if ($password !== $password_confirm) {
            $request = $request->withAttribute("errors", [
                    "New password and new password confirmation do not match."
                ]
            );

            $this->show_password_recovery_confirm_form($request, $response, $args);

            return;
        }

        $gump = new GUMP();
        $password_validation = $gump->validate(["password", $password], ["password" => "min_len,6|max_len,255"]);

        if ($password_validation !== true) {
            $request = $request->withAttribute("errors", [
                    "Invalid password: " . implode("; ", $password_validation)
                ]
            );

            $this->show_password_recovery_confirm_form($request, $response, $args);

            return;
        }

        $user_auth_dao->update_local_user_reset_password($user["account_id"], $password);

        $request = $request->withAttribute("password_recovery_apply_submitted", true);

        $this->show_password_recovery_confirm_form($request, $response, $args);
    }

    function show_password_recovery_confirm_form(Request $request, Response $response, $args = []) {
        $params = $request->getParams();

        $errors = $request->getAttribute("errors");
        $password_recovery_apply_submitted = $request->getAttribute("password_recovery_apply_submitted");

        $this->container->get('view')->render($response, "password_recovery_confirm_form.twig", [
            "errors" => !empty($errors) ? $errors : false,
            "page_title" => "Password Recovery",
            "password_reset_selector" => $params["password_reset_selector"],
            "password_reset_token" => $params["password_reset_token"],
            "password_recovery_apply_submitted" => isset($password_recovery_apply_submitted) ? $password_recovery_apply_submitted : false
        ]);
    }

}
