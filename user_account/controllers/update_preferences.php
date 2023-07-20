<?php
function update_preferences(\Slim\Route $route){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	//require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/GUMP/gump.class.php";
	//url parameters matched in the route
	$params = $route->getParams();
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$useraccount = new \user_account\models\UserAccountDao($db_resource,$final_global_template_vars["session_key"]);
	$post = $app->request()->post();
	$errors = false;
	//will probably need the following commented code if we add anything other than groups
	/*
	$gump = new GUMP();
	$rules = array(
		"name" => "required"
	);
	$validated = $gump->validate($app->request()->post(), $rules);
	$errors = array();
	if($validated !== TRUE){
		$errors = \swpg\models\utility::gump_parse_errors($validated);
	}
	*/	
	
	if(!$errors){
		$useraccount->update_preferences($post,$_SESSION[$final_global_template_vars["session_key"]]["account_id"]);
		$app->flash('success', 'You have successfully updated your preferences.');
		$app->redirect($final_global_template_vars["path_to_this_module"] . "/preferences");
	}else{
		$env = $app->environment();
		$env["swpg_validation_errors"] = $errors;
	}
}
?>