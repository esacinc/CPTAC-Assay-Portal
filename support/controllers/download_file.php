<?php
function download_file($file_id=false){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/support.class.php";
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$support = new Support($db_resource,$final_global_template_vars["session_key"],$final_global_template_vars["site_key"]);

	$file_info = $support->get_support_file_contents($file_id);

	header("Content-type: {$file_info['file_type']}");
	header("Content-Disposition: attachment; filename={$file_info['file_name']}");
	echo $file_info['file_content'];
	die();
}
?>