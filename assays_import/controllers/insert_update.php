<?php
/**
 * @desc Import Assays: controller for inserting and updating data
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
function insert_update( $import_log_id = false ) {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays_import.class.php";
  require_once $_SERVER["DOCUMENT_ROOT"] . "/assays/models/assays.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $import = new AssaysImport( $db_resource, $final_global_template_vars["session_key"] );
  $assay = new Assay( $db_resource );

  $data = array();
  $current_values = array();
  $laboratory_name = "";
  $data["session"] = $_SESSION[$final_global_template_vars["session_key"]];

  if( $app->request()->post() ) {
    $current_values = $app->request()->post();

    $data['sop_files'] = $import->get_sop_files( $current_values['import_log_id'] );
    $data['uploaded_files'] = isset($current_values['uploaded_files']) ? $import->get_sop_files_by_id( $current_values['uploaded_files'] ) : false;

    // $data['uploaded_sop_file_types'] = isset($current_values['sop_file_types']) ? $current_values['sop_file_types'] : false;
    $data['publications'] = $import->get_publications( $current_values['import_log_id'] );
    // Posted Publications
    if( isset($current_values['import_log_id']) && isset($current_values["publication_citation"]) ) {
      for($i = 0; $i < count($current_values["publication_citation"]); $i++) {
        $data['submitted_publications'][] = array(
            "publication_citation" => $current_values["publication_citation"][$i]
          , "publication_url" => $current_values["publication_url"][$i]
        );
      }
    }
  } elseif ( $import_log_id ){
    $current_values = $import->get_assay_import_record( $import_log_id );
    $data['sop_files'] = $import->get_sop_files( $import_log_id );
    $data['publications'] = $import->get_publications( $import_log_id );

    // Get the user's roles.
    $user_role_ids = isset($data["session"]["user_role_list"]) 
      ? $data["session"]["user_role_list"] : array();
    // Get the laboratory metadata via the import_log_id GET variable.
    $data["laboratory_data"] = $assay->get_laboratory_by_import_log_id( $import_log_id );
    // Get the laboratory name for the page title (superadmin only).
    $laboratory_name = in_array(4, $user_role_ids) ? ": ".$data["laboratory_data"]["laboratory_name"] : "";
  }

  // Get all SOP File types.
  $data['sop_file_types'] = $import->get_sop_file_types();

  // Throw a 404 if no values are returned. (This means either user is not in a privileged group or an incorrect id was supplied.)
  if((!$current_values && $app->request()->post()) || (!$current_values && $import_log_id)) $app->notFound();

  // Get assay types
  $data["assay_types"] = $import->get_assay_types();

  // Get peptide_standard_purity_types
  $data['peptide_standard_purity_options'] = $import->get_peptide_standard_purity_types();




  $data = array_merge( $current_values, $data );

  // Render
  $app->render(
    "insert_update.php"
    ,array(
      "page_title" => "Add Import Metadata".$laboratory_name
      ,"hide_side_nav" => true
      ,"data" => $data
      ,"errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
    )
  );
}
?>