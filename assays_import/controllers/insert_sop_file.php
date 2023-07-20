<?php
/**
 * @desc Import Assays: controller for inserting SOP file data into the database
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
function insert_sop_file() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once ($final_global_template_vars["absolute_path_to_this_module"] . "/models/assays_import.class.php");
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $import = new AssaysImport( $db_resource, $final_global_template_vars["session_key"] );

  $data = $import->insert_sop_file( $app->request()->post('file_data') );

  echo json_encode($data);
}