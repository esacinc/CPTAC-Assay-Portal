<?php
function get_notes_totals() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $assay = new AssaysManage( $db_resource, $final_global_template_vars["session_key"] );

  $post = $app->request()->post();
  $data = false;

  if( isset($post['protein_id']) && !empty($post['protein_id']) ) {
    $protein_id = (int)$post['protein_id'];
    $data = $assay->get_notes_totals( $protein_id );
  }

  echo json_encode($data);
}
?>