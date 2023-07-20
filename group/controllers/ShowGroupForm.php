<?php

namespace group\controllers;
use Slim\Http\Request;
use Slim\Http\Response;

use group\models\GroupDao;

use core\controllers\Controller;

class ShowGroupForm extends Controller {



function show_group_form(Request $request, Response $response, $args = []){

  //$this->container->get('logger')->info($group_id);
	global $final_global_template_vars;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$group = new \group\models\GroupDao($db_resource,$final_global_template_vars["session_key"]);

	$group_hierarchy = $group->get_group_hierarchy("--");
	$flat_group_hierarchy = $group->flatten_group_hierarchy($group_hierarchy);
	$group_id = $args['group_id'];

	$current_values = false;
	if($request->isPost()){
		$current_values = $request->isPost();
	}elseif($group_id){
		$current_values = $group->get_group_record($group_id);
	}

	$title = ($group_id) ? "Update" : "Create";
  /*
	$app->render('group_form.php',array(
		"page_title" => "{$title} Group"
		,"group_data" => $current_values
		,"groups" => $flat_group_hierarchy
		,"errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
	));
	*/

	//$this->container->get('logger')->info("TEST");

	$view = $this->container->get('view');
					$view->render(
							$response
							, 'group_form.php'
							,array(
								"page_title" => "{$title} Group"
								,"group_data" => $current_values
								,"groups" => $flat_group_hierarchy
								,"errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
							)
					);

					return $response;


}
}
?>
