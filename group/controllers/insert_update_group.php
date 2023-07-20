<?php
function insert_update_group(\Slim\Route $route){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	//require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/GUMP/gump.class.php";
	//url parameters matched in the route
	$params = $route->getParams();
	$group_id = isset($params["group_id"]) ? $params["group_id"] : false;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$group = new \group\models\GroupDao($db_resource,$final_global_template_vars["session_key"]);
	$gump = new GUMP();
	$rules = array(
		"name" => "required"
		,"abbreviation" => "required"
		,"state" => "alpha_numeric"
		,"zip" => "numeric|exact_len,5"
		,"group_parent" => "numeric"
	);
	$validated = $gump->validate($app->request()->post(), $rules);
	$errors = array();
	if($validated !== TRUE){
		$errors = \swpg\models\utility::gump_parse_errors($validated);
	}
	
	if(!$errors){
		$group->insert_update_group($app->request()->post(),$group_id);
		$app->redirect($final_global_template_vars["path_to_this_module"]);
	}else{
		$env = $app->environment();
		$env["swpg_validation_errors"] = $errors;
	}
}
?>