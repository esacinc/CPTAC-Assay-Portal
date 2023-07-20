<?php

namespace authenticate\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;
use authenticate\models\Google\GoogleUserAuthenticationService;

class Logout extends Controller {

    function logout(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        unset($_SESSION[$final_global_template_vars["session_key"]]);

        GoogleUserAuthenticationService::clearSessionDataEntries();

        return $response->withRedirect($final_global_template_vars["login_url"]);
    }

}