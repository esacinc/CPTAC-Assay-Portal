<?php
function data_entry(){
	$app = \Slim\Slim::getInstance();
	$env = $app->environment();
	global $final_global_template_vars;

	// require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
	// require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/labkey.class.php";

	$app->render('data_entry.php',array(
		"page_title" => "Data Entry"
		,"hide_side_nav" => true
		//,"login_data" => $login_data
	));
}
?>