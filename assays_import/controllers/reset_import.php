<?php
/**
 * @desc Import Assays: controller for resetting an import.
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
function reset_import() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays_import.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $import = new AssaysImport( $db_resource, $final_global_template_vars["session_key"] );

  $import_log_id = (int)$app->request()->post('import_log_id');

  $data = $import->reset_import( $import_log_id );

  $app->flash('success', 'The import has been successfully reset.');

  echo json_encode($data);
}
?>