<?php
/**
 * @desc Import Assays: controller for validating data
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
function insert_update_assays_import(){
  $app = \Slim\Slim::getInstance();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays_import.class.php";
  //require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/GUMP/gump.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $import = new AssaysImport( $db_resource, $final_global_template_vars["session_key"] );
  $gump = new GUMP();
  $post = $app->request()->post();
  $import_log_id = !empty($post["import_log_id"]) ? $post["import_log_id"] : false;

  // Default rules
  $rules = array();

  $rules = array(
    "instrument" => "required"
    ,"matrix" => "required"
    ,"matrix_amount_and_units" => "required"
    ,"quantification_units" => "required"
    ,"internal_standard" => "required"
    //,"peptide_standard_purity" => "required"
    ,"peptide_standard_purity_types_id" => "required"
    ,"protein_species_label" => "required"
    ,"data_type" => "required"
    ,"lc" => "required"
    ,"column_packing" => "required"
    ,"column_dimensions" => "required"
    ,"column_temperature" => "required"
    ,"flow_rate" => "required"
    ,"mobile_phase_a" => "required"
    ,"mobile_phase_b" => "required"
    ,"celllysate_path" => "required"
  );

  // Assay types rules
  $rules_assay_types = array();

  if(isset($post["assay_types_id"])) {
    switch ( $post["assay_types_id"] ) {
      case "1":
        $rules_assay_types = array(
          "enrichment_method" => "required"
          // ,"affinity_reagent_type" => "required"
          ,"antibody_vendor" => "required"
          ,"media" => "required"
          // ,"antibody_portal_url" => "required"
        );
        break;
      case "2":
        $rules_assay_types = array(
          "fractionation_approach" => "required"
          ,"column_material" => "required"
          ,"conditions" => "required"
          ,"number_of_fractions_collected" => "required"
          ,"number_of_fractions_analyzed" => "required"
          ,"fraction_combination_strategy" => "required"
        );
        break;
    }
  }

  // // Validate the files
  // $errors_files = array();

  // if( isset($posted_files) ) {

  //   $allowed_mime_types = $final_global_template_vars['file_validation']['allowed_mime_types'];
  //   $max_size = $final_global_template_vars['file_validation']['max_size'];

  //   // Validate the sop files
  //   foreach( $posted_files as $file_key => $file_value ) {
  //     if( !empty($file_value['tmp_name']) ) {
  //       for($i = 0; $i < count($file_value['tmp_name']); $i++) {
  //         if( !empty( $file_value['type'][$i] ) && !in_array( $file_value['type'][$i], $allowed_mime_types ) ) {
  //           // Invalid file type
  //           $errors_files[$file_key."_".($i+1)] = $file_key." ".($i+1).' file is an invalid file type';
  //         }
  //         if( !empty($file_value['size'][$i]) && ($file_value['size'][$i] > $max_size) ) {
  //           // Invalid file size
  //           $errors_files[$file_key."_".($i+1)] = $file_key." ".($i+1).' file is too large';
  //         }

  //       }
  //     }
  //   }

  // }



  // Merge the default rules and the assay types rules
  $rules = array_merge( $rules, $rules_assay_types );
  $validated = $gump->validate($post, $rules);
  $errors = array();
  if($validated !== TRUE){
    $errors = \swpg\models\utility::gump_parse_errors($validated);
  }
  // // Merge the default errors and the file validation errors
  // $errors = array_merge($errors, $errors_files);
  
  if(!$errors ){
    $import->insert_update_assays_import( $post, $import_log_id );
    $message = $import_log_id 
      ? 'updated.' 
      : 'entered into the database.';
    $app->flash('success', 'Assay parameters successfully '.$message);
    $app->redirect($final_global_template_vars["path_to_this_module"]);
  } else {
    $current_values = $app->request()->post();
    $env = $app->environment();
    $env["swpg_validation_errors"] = $errors;
  }
}
?>