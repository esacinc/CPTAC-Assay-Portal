<?php
namespace authenticate\controllers;



use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

class ShowLoginForm extends Controller {

    function show_login_form(Request $request, Response $response, $args = []){
        global $final_global_template_vars;

        $user_role_list = $_SESSION[$final_global_template_vars["session_key"]]['user_role_list'];

        //$this->container->get('logger')->info(var_export($user_role_list, true));

        if(!empty($user_role_list)) {

            if (!empty(array_diff($user_role_list, $final_global_template_vars["role_permissions"]["public_upload"]["upload"]))) {
                return $response->withRedirect($final_global_template_vars['landing_page']);
            }

            if (!empty(array_intersect($user_role_list, $final_global_template_vars["role_permissions"]["public_upload"]["upload"]))) {
                //$this->container->get('logger')->info('redirect to public upload ' . $final_global_template_vars['public_upload']);
                return $response->withRedirect($final_global_template_vars['public_upload']);
            }
        }

        $errors = $request->getAttribute("errors");
        $this->container->get('view')->render($response, 'login_form.twig', array(
            "page_title" => "Login"
        , "hide_page_header" => true
        , "errors" => !empty($errors) ? $errors : false
        ));

    }

}
