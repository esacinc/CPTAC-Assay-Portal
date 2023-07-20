<?php
function insert_update_category(\Slim\Route $route){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/support.class.php";
	//require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/GUMP/gump.class.php";
	//url parameters matched in the route
	$params = $route->getParams();
	$category_id = isset($params["category_id"]) ? $params["category_id"] : false;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$support = new Support($db_resource,$final_global_template_vars["session_key"],$final_global_template_vars["site_key"]);
	$gump = new GUMP();
	$rules = array(
		"category_name" => "required"
	);
	$validated = $gump->validate($app->request()->post(), $rules);
	$errors = array();
	if($validated !== TRUE){
		$errors = \swpg\models\utility::gump_parse_errors($validated);
	}
	if(!$errors){
		$support->manage_categories($app->request()->post(),$category_id);
		$app->redirect($final_global_template_vars["path_to_this_module"] . '/categories');
	}else{
		$env = $app->environment();
		$env["swpg_validation_errors"] = $errors;
	}
}
?>