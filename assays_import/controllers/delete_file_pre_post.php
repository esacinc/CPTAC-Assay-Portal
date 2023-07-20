<?php
/**
 * @desc Import Assays: controller for deleting SOP files pre-post, completely deleting 
 * the records in the sop_files and sop_files_join tables.
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
function delete_file_pre_post() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays_import.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $import = new AssaysImport( $db_resource, $final_global_template_vars["session_key"] );

  $file_id = (int)$app->request()->post('file_id');

  $data = $import->delete_file_pre_post( $file_id );

  echo json_encode($data);
}
?>