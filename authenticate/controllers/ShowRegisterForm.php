<?php
namespace authenticate\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

class ShowRegisterForm extends Controller {

    function show_register_form(Request $request, Response $response, $args = []) {

        $errors = $request->getAttribute("errors");
        $registration_submitted = $request->getAttribute("registration_submitted");

        $this->container->get('view')->render($response, 'register_form.twig', [
                "page_title" => "User Registration"
                ,"hide_page_header" => true
                ,"errors" => !empty($errors) ? $errors : false
                ,"registration_submitted" => isset($registration_submitted) ? $registration_submitted : false
            ]
        );
    }

}