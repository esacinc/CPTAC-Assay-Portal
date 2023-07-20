<?php
/**
 * @desc Import module settings
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 * Note that you are able to use any key that exists in
 * the global settings, and it will overwrite it
 *
 */

$user_roles = isset($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"])
  ? $_SESSION[$final_global_template_vars["session_key"]]["user_role_list"] : array();

$initially_hidden = in_array(4, $user_roles) ? false : true;

$swpg_module_settings = array(
  "module_name" => "Manage Imports"
  ,"module_description" => "Manage assay imports"
  ,"module_icon_css_classes" => "fa fa-fw fa-plus-square"
  ,"sort_order" => 2
  ,"menu_hidden" => true
  ,"navbar" => "/site/templates/admin_navbar_v1_update.php"
  ,"layout_template_name" => "swpg_bootstrap_admin_non_responsive_v1.twig"

  ,"pages" => array(
    // array(
    //   "label" => "New Import"
    //   ,"path" => "/new"
    //   ,"display" => $apply_permissions("import", "submit_access")
    // ),
    array(
      "label" => "Browse Imports"
      ,"path" => "/"
      //,"display" => $apply_permissions("import", "submit_access")
    ),
    array(
      "label" => "Enter New Import Metadata"
      ,"path" => "/insert_update"
      //,"display" => $apply_permissions("import", "submit_access")
    )
  )
  ,"file_validation" => array(
    "max_size" => 10000000
    ,"allowed_mime_types" => array(
      "application/pdf"
      // ,"application/msword"
      // ,"application/vnd.openxmlformats-officedocument.wordprocessingml.document"
      // ,"application/vnd.ms-excel"
      // ,"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
      // ,"application/vnd.ms-powerpoint"
      // ,"application/vnd.openxmlformats-officedocument.presentationml.presentation"
      // ,"image/jpeg"
      // ,"image/pjpeg" //freakin IE
      // ,"image/png"
      // ,"image/x-png" //freakin IE
      // ,"image/gif"
      // ,"text/plain"
      // ,"application/rtf"
    )
    ,"imagemagick_image_types" => array(
        "image/jpeg"
      , "image/pjpeg" //freakin IE
      , "image/png"
      , "image/x-png" //freakin IE
      , "image/gif"
    )
  )
  ,"import_logged_database_tables" => array(
      "analyte_peptide"
    , "lod_loq_comparison"
    , "lod_loq_comparison_data_failed"
    , "panorama_chromatogram_images"
    , "panorama_chromatogram_images_failed"
    , "panorama_response_curve_images"
    , "panorama_response_curve_images_failed"
    , "panorama_validation_sample_data"
    , "panorama_validation_sample_data_failed"
    , "panorama_validation_sample_images"
    , "panorama_validation_sample_images_failed"
    , "protein"
    , "response_curves_data"
    , "response_curves_data_failed"
    , "imports_executed_log"
    , "missing_uniprot_ids"
  )
  ,"browse_fields" => array(
      "import_log_id" => array("handle" => "import_log_id", "label" => "ID","filter" => false, "initially_hidden" => false)
    , "laboratory_name" => array("handle" => "laboratory_name", "label" => "Data Provider", "comparison_default" => "start_with", "filter" => false, "initially_hidden" => false)
    , "panorama_directory" => array("handle" => "panorama_directory", "label" => "Panorama Directory", "comparison_default" => "start_with", "filter" => false, "initially_hidden" => false)
    , "created_date" => array("handle" => "created_date", "label" => "Created", "comparison_default" => "start_with", "filter" => false, "initially_hidden" => false)
    , "manage" => array("handle" => "manage", "label" => "Manage", "filter" => false, "initially_hidden" => false)
  )
  , "remove_side_nav" => true
);

/*

 * Changes to database schema - 2014-04-02

 !!! NOTE !!!
 The analytical_validation_of_assay table is not being queried at all!!!


 ** Query flow:

 protein >>
   import_log_id (has the laboratories_id (group_id), gets via import_log_id: assay_parameters_new_id, publications, and sops) >

 assay_parameters_new (tied to the protein table via assay_parameters_new_id) >>
   assay_types_id (gets the assay_types-related data)

 analyte_peptide (tied to the protein via protein_id) >>
   *** get everything else via analyte_peptide_id ***


 ** Additions:

 Protein table needs these field added:

 import_log_id
 assay_parameters_new_id
 protein_species_id


 ** Query changes:

 Everywhere there are LEFT JOINS such as:

 - LEFT JOIN assay_parameters on assay_parameters.analyte_peptide_id = analyte_peptide.analyte_peptide_id

 It needs to change to:

 - LEFT JOIN assay_parameters on assay_parameters.import_log_id = import_log.import_log_id

This LEFT JOIN needs to be added:

 - LEFT JOIN analyte_peptide on analyte_peptide.protein_id = protein.protein_id


*/
?>
