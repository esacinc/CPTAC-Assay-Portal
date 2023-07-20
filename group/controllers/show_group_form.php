<?php
function show_group_form($group_id=false){
	$app = \Slim\Slim::getInstance();
	$env = $app->environment();
	global $final_global_template_vars;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$group = new \group\models\GroupDao($db_resource,$final_global_template_vars["session_key"]);
	
	$group_hierarchy = $group->get_group_hierarchy("--");
	$flat_group_hierarchy = $group->flatten_group_hierarchy($group_hierarchy);

	$current_values = false;
	if($app->request()->post()){
		$current_values = $app->request()->post();
	}elseif($group_id){
		$current_values = $group->get_group_record($group_id);
	}

	$title = ($group_id) ? "Update" : "Create";
	$app->render('group_form.php',array(
		"page_title" => "{$title} Group"
		,"group_data" => $current_values
		,"groups" => $flat_group_hierarchy
		,"errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
	)); 
}
?>