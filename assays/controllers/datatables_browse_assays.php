<?php
function datatables_browse_assays() {
  $app = \Slim\Slim::getInstance();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/config/settings.php";
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
  $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
  $db_resource = $db_conn->get_resource();
  $assay = new Assay($db_resource,$final_global_template_vars["session_key"]);
  $data = false;

  $side_bar_filter = array();

  $sortable_key_fields = array_keys($final_global_template_vars['datatables'][0]['fields']);

  $side_bar_filter = json_decode($app->request()->post('sidebar_filter'),true);

  $data = $assay->browse_assays($sortable_key_fields[$app->request()->post('iSortCol_0')]
      ,$app->request()->post('sSortDir_0')
      ,$app->request()->post('iDisplayStart')
      ,$app->request()->post('iDisplayLength')
      ,$app->request()->post('sSearch')
      ,$final_global_template_vars['datatables'][0]['fields']
      ,$side_bar_filter);

  $data['sEcho'] = (int)$app->request()->post('sEcho');
  echo json_encode($data);
  die();
}
?>