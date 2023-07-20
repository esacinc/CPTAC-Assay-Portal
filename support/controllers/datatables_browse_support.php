<?php
function datatables_browse_support(){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/support.class.php";
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$support = new Support($db_resource,$final_global_template_vars["session_key"], $final_global_template_vars["site_key"]);

	$column_filter = $app->request()->post('column_filter');
	$column_filters = $column_filter ? json_decode($column_filter) : false;

	$sortable_key_fields = array_keys($final_global_template_vars["browse_fields"]);

	$data = $support->browse_support($sortable_key_fields[$app->request()->post('iSortCol_0')], $app->request()->post('sSortDir_0')
	  , $app->request()->post('iDisplayStart'), $app->request()->post('iDisplayLength'), $app->request()->post('sSearch')
	  , $column_filters, $final_global_template_vars["browse_fields"]);
	$data['sEcho'] = $app->request()->post('sEcho');
	echo json_encode($data);
	die();
}
?>