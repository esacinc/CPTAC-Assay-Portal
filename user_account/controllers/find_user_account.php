<?php
function find_user_account(){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$useraccount = new \user_account\models\UserAccountDao($db_resource,$final_global_template_vars["session_key"]);
	$search = $app->request()->post('search');
	
	$results = $useraccount->find_user_account($search);
	echo json_encode($results);
	die();
}
?>