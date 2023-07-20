<?php

namespace modules\controllers;

use \core\controllers\Controller;
use Slim\Http\Request;
use Slim\Http\Response;

class ShowDashboard extends Controller {

    function show_dashboard(Request $request, Response $response, $arg = []) {
        global $final_global_template_vars;

        // Check permissions and accordingly set the 'no_permissions' variable (boolean)
        $data["no_permissions"] = empty($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"]) ? true : false;
        $data["page_title"] = "Dashboard";

        if (empty($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"])) {
            return $response->withRedirect('/group/choose_laboratory');
        } else {

            $user_role_list = $_SESSION[$final_global_template_vars["session_key"]]["user_role_list"];

            if (!empty(array_diff($user_role_list, $final_global_template_vars["role_permissions"]["public_upload"]["upload"]))) {
                $this->container->get('view')->render($response,
                    'modules_dashboard.php'
                    , $data
                );
            }

            if (!empty(array_intersect($user_role_list, $final_global_template_vars["role_permissions"]["public_upload"]["upload"]))) {
                return $response->withRedirect($final_global_template_vars['public_upload']);
            }

            return $response->withRedirect($final_global_template_vars['$final_global_template_vars["access_denied_url"]']);
        }
    }

}