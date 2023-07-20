<?php

namespace support\controllers;

use Slim\Http\Request;
use Slim\Http\Response;


use support\models\Support;
use assays\models\Assay;

use core\controllers\Controller;


class ShowSupportForm extends Controller
{

	function show_support_form(Request $request, Response $response, $args = [])
	{

		global $final_global_template_vars;
		require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/support.class.php";
		


		$view = $this->container->get('view');
		/*
        $view->render($response, 'support_form.php', array(
            "page_title" => "Website Support"
        , "support_data" => $current_values
        , "categories" => $categories
        , "configuration" => $configuration
        , "captcha_generation" => "/" . $_SERVER["CORE_TYPE"] . "/3rd_party/securimage/securimage_show.php"
        , "errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
        ));
        */
		$view->render($response, 'support_contact_info.php', array(
			"page_title" => "Website Support"
		));

		return $response;
	}
}