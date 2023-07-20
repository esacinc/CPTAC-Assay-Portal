<?php

namespace group\controllers;
use Slim\Http\Request;
use Slim\Http\Response;

use group\models\GroupDao;

use \GUMP;

use core\controllers\Controller;


class InsertUpdateGroup extends Controller {

function insert_update_group(Request $request, Response $response, $args = []){

	global $final_global_template_vars;
	//require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/GUMP/gump.class.php";
	//url parameters matched in the route

	//$this->container->get('logger')->info("TEST1");
	//$params = $route->getParams();
	$group_id = isset($args["group_id"]) ? $args["group_id"] : false;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();

  //$this->container->get('logger')->info("TEST1");
	$group = new \group\models\GroupDao($db_resource,$final_global_template_vars["session_key"]);
	$gump = new GUMP();

	$post = $request->getParsedBody();
	$rules = array(
		"name" => "required"
		,"abbreviation" => "required"
		,"state" => "alpha_numeric"
		,"zip" => "numeric|exact_len,5"
		,"group_parent" => "numeric"
	);
	$validated = $gump->validate($post, $rules);
	$errors = array();

  //$this->container->get('logger')->info("TEST");

	//$this->container->get('logger')->info($group_id);

	if($validated !== TRUE){
		$errors = \swpg\models\utility::gump_parse_errors($validated);
	}

	if(!$errors){
		$group->insert_update_group($post,$group_id);
		return $response->withRedirect($final_global_template_vars["path_to_this_module"]);
	}else{
		$env = $app->environment();
		$env["swpg_validation_errors"] = $errors;
	}
}
}
?>
