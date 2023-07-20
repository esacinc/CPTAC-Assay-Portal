<?php
function show_settings_form(){
	$app = \Slim\Slim::getInstance();
	$env = $app->environment();
	global $final_global_template_vars;
	require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/support.class.php";
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$support = new Support($db_resource,$final_global_template_vars["session_key"],$final_global_template_vars["site_key"]);
	
	$current_values = false;
	if($app->request()->post()){
		$current_values = $app->request()->post();
	}else{
		$current_values = $support->get_configuration();
	}

	
	$app->render('settings_form.php',array(
		"page_title" => "Support Settings/Configuration" 
		,"settings_data" => $current_values
	)); 
}
?>