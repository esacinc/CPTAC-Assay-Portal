<?php
function insert_update_settings(\Slim\Route $route){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/support.class.php";
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$support = new Support($db_resource,$final_global_template_vars["session_key"],$final_global_template_vars["site_key"]);

	$support->update_configuration($app->request()->post());
	$app->redirect($final_global_template_vars["path_to_this_module"] . "/browse");
}
?>