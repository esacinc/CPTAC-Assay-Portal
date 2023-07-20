<?php
function delete_note() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $assay = new Assay( $db_resource, $final_global_template_vars["session_key"] );

  $post = $app->request()->post();
  $data = false;

  $user_id = (int)$_SESSION[$final_global_template_vars["session_key"]]["account_id"];
  $note_id = (int)$app->request()->post('note_id');

  $data = $assay->delete_note( $note_id );

  if($data) {
    echo json_encode($data);
  }
}
?>