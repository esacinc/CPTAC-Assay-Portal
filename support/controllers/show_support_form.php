<?php 
function show_support_form(){
	$app = \Slim\Slim::getInstance();
	$env = $app->environment();
	global $final_global_template_vars;
	require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/support.class.php";
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$support = new Support($db_resource,$final_global_template_vars["session_key"],$final_global_template_vars["site_key"]);
	$categories = $support->get_categories();
	$configuration = $support->get_configuration();
	
	$current_values = array();
	if($app->request()->post()){
		$current_values = $app->request()->post();
	}
	
	$app->render('support_form.php',array(
		"page_title" => "Website Support"
		,"support_data" => $current_values
		,"categories" => $categories
		,"configuration" => $configuration
		,"captcha_generation" => "/" . $_SERVER["CORE_TYPE"] . "/3rd_party/securimage/securimage_show.php"
		,"errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
	)); 
}
?>