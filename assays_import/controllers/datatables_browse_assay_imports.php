<?php
/**
 * @desc Import Assays: controller for browsing assay import sets for datatables
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
function datatables_browse_assay_imports() {
  $app = \Slim\Slim::getInstance();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays_import.class.php";
  $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
  $db_resource = $db_conn->get_resource();
  $import = new AssaysImport( $db_resource, $final_global_template_vars["session_key"] );
  
  $column_filter = $app->request()->post('column_filter');
  $column_filters = $column_filter ? json_decode($column_filter) : false;

  $sortable_key_fields = array_keys($final_global_template_vars["browse_fields"]);

  $data = $import->browse_assay_imports(
      $sortable_key_fields[$app->request()->post('iSortCol_0')]
    , $app->request()->post('sSortDir_0')
    , $app->request()->post('iDisplayStart')
    , $app->request()->post('iDisplayLength')
    , $app->request()->post('sSearch')
    , $column_filters
    , $final_global_template_vars["browse_fields"]
  );

  $data['sEcho'] = (int)$app->request()->post('sEcho');
  echo json_encode($data);
  die();
}
?>