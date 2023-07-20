<?php
function email_csv() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $assay = new Assay( $db_resource, $final_global_template_vars["session_key"] );

  $post = $app->request()->post();
  $csv_link = false;

  $user_id = (int)$_SESSION[$final_global_template_vars["session_key"]]["account_id"];
  $laboratory_id = (int)$app->request()->post('laboratory_id');
  $import_set_id = (int)$app->request()->post('import_set_id');

  // Create the CSV file
  if($laboratory_id && $import_set_id) {
    // Get the array for the CSV
    $notes = $assay->get_notes_by_import_set_id( $import_set_id );

    if($notes) {
      $path_to_temp_directory = $_SERVER['DOCUMENT_ROOT'].'/swpg_files/cptac/temp/';
      $path_to_temp_directory_via_http = $_SERVER['DOCUMENT_ROOT'].'/swpg_files/cptac/temp/';
      $filename = "CPTAC_".date("YmdHis")."_all_notes.csv";
      $fp = fopen($path_to_temp_directory.$filename, 'w');
      fputcsv($fp, array('gene_symbol', 'peptide_sequence', 'note_content', 'note_submitted_by'));
      foreach ($notes as $note) {
        fputcsv($fp, $note);
      }
      fclose($fp);
    }

    $csv_link = 'http://' . $_SERVER["SERVER_NAME"] . $path_to_temp_directory_via_http . $filename;

  }
  
  echo json_encode($csv_link);
}
?>