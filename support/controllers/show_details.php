<?php
function show_details($support_id=false){
	$app = \Slim\Slim::getInstance();
	$env = $app->environment();
	global $final_global_template_vars;
	require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/support.class.php";
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$support = new Support($db_resource,$final_global_template_vars["session_key"],$final_global_template_vars["site_key"]);

	$support_info = $support->get_support_request($support_id);
	$support_file_info = $support->get_support_file_info($support_id);

	$app->render('details.php',array(
		"page_title" => "Request Details"
		,"support_data" => $support_info
		,"support_file_data" => $support_file_info
	)); 
}
?>