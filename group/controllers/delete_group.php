<?php
function delete_group(){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$group = new \group\models\GroupDao($db_resource,$final_global_template_vars["session_key"]);
	$delete_ids = json_decode($app->request()->post("id"));
	foreach($delete_ids as $single_id){
		$group->delete_group($single_id);
	}
}
?>