<?php

namespace authenticate\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use authenticate\models\Db\UserAuthenticationDao;
use swpg\models\db;
use user_account\models\UserAccountDao;
use \GUMP;

class RegistrationConfirm extends Controller {

    function confirm_registration(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $register_selector = $request->getParam("register_selector");
        $register_token = $request->getParam("register_token");

        if (empty($register_selector)) {
            $request = $request->withAttribute("errors", [
                    "No register selector provided."
                ]
            );

            $this->show_registration_confirm($request, $response, $args);

            return;
        } else if (empty($register_token)) {
            $request = $request->withAttribute("errors", [
                    "No register token provided."
                ]
            );

            $this->show_registration_confirm($request, $response, $args);

            return;
        }

        $db = (new db($final_global_template_vars["db_connection"]))->get_resource();
        $user_account_dao = new UserAccountDao($db, $final_global_template_vars["session_key"]);
        $user_auth_dao = new UserAuthenticationDao($db, (new db($final_global_template_vars["core_framework_db"]))->get_resource(), $user_account_dao);
        $user = $user_auth_dao->get_local_user_by_registration_data($register_selector, $final_global_template_vars["password_reset_expiration_interval"]);

        //@@@CAP-50 - user account and password recovery updates
        if(!empty($user)) {
            if($user["acceptable_use_policy"] == 1) {
                $request = $request->withAttribute("registration_confirmed", true);

                $this->show_registration_confirm($request, $response, $args);
            }
        }

        //@@@CAP-50 - user account and password recovery updates
        if (empty($user) || !hash_equals(hex2bin($user["password_reset_token"]), hash("sha256", hex2bin($register_token), true))) {

            $request = $request->withAttribute("errors", [
                    "Invalid registration confirmation."
                ]
            );

            $this->show_registration_confirm($request, $response, $args);

            return;
        }

        $user_auth_dao->update_local_user_registration($user["account_id"]);

        $request = $request->withAttribute("registration_confirmed", true);

        $this->show_registration_confirm($request, $response, $args);
    }

    function show_registration_confirm(Request $request, Response $response, $args = []) {
        $params = $request->getParams();

        $errors = $request->getAttribute("errors");
        $registration_confirmed = $request->getAttribute("registration_confirmed");

        $this->container->get('view')->render($response, "registration_confirm.twig", [
            "errors" => !empty($errors) ? $errors : false,
            "page_title" => "Registration Confirmation",
            "registration_confirmed" => isset($registration_confirmed) ? $registration_confirmed : false
        ]);
    }

}
