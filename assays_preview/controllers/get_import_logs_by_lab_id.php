<?php
function get_import_logs_by_lab_id() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $assay = new Assay( $db_resource, $final_global_template_vars["session_key"] );

  $post = $app->request()->post();
  $data = false;

  if( isset($post['laboratory_id']) && !empty($post['laboratory_id']) ) {
    $laboratory_id = (int)$post['laboratory_id'];
    $data = $assay->get_import_logs_by_lab_id( $laboratory_id );
  }

  echo json_encode($data);
}
?>