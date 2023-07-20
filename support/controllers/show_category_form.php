<?php
function show_category_form($category_id=false){
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
	}elseif($category_id){
		$current_values = $support->get_categories($category_id);
		$current_values = (isset($current_values[0])) ? $current_values[0] : false;
	}

	$title = ($category_id) ? "Update" : "Create";
	$app->render('category_form.php',array(
		"page_title" => "{$title} Category"
		,"category_data" => $current_values
		,"errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
	)); 
}
?>