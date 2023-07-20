<?php
function show_preferences_form(){
	$app = \Slim\Slim::getInstance();
	$env = $app->environment();
	global $final_global_template_vars;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$useraccount = new \user_account\models\UserAccountDao($db_resource,$final_global_template_vars["session_key"]);

	$current_values = false;
	if($app->request()->post()){
		$current_values = $app->request()->post();
	}else{
		$current_values = $useraccount->get_user_preferences($_SESSION[$final_global_template_vars["session_key"]]["account_id"]);
	}
	
	$app->render('preferences_form.php',array(
		"page_title" => "Preferences"
		,"data" => $current_values
		,"errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
	)); 
}
?>