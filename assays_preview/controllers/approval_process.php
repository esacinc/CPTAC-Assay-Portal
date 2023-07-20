<?php
function approval_process() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $assay = new Assay( $db_resource, $final_global_template_vars["session_key"] );

  $post = $app->request()->post();
  $data = false;
  $updated_ids = array();

  if( !empty($post) ) {
    if( !empty($post['ids_approved']) ) {
      $ids = json_decode($post['ids_approved']);
      foreach($ids as $id) {
        $updated_ids[] = $assay->approval_process( (int)$id, true );
      }
    }

    if( !empty($post['ids_disapproved']) ) {
      $ids = json_decode($post['ids_disapproved']);
      foreach($ids as $id) {
        $updated_ids[] = $assay->approval_process( (int)$id, false );
      }
    }
  }

  echo json_encode($updated_ids);
}
?>