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
//@@@CAP-110 fix SOP and metadata template downloads
$swpg_module_settings = array(
  "public_navbar" => "/site/templates/public_navbar_update.php"
  ,"layout_template_name" => "swpg_bootstrap_public_upload.twig"
  ,"preview_layout_template_name" => "swpg_bootstrap_admin_non_responsive_v1.twig"

  ,"module_name" => "Import Assays"
  ,"module_description" => "Import assays from Panorama."
  ,"module_icon_css_classes" => "fa fa-fw fa-plus-square fa-eye"
  //@@@CAP-110 - fix downloads on metadata page
  ,"sop_path" => $_SERVER['DOCUMENT_ROOT']."/public_upload/library/sop_files/"
  //,"sop_template_path" => $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/sop_files/CPTAC_Assay_Portal_SOP_Template.pdf"
  ,"sop_template_path" => $_SERVER['DOCUMENT_ROOT']."/public_upload/library/template_files/SOP_Template_20140723.docx"
  //,"metadata_template_path" => $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/sop_files/CPTAC_Assay_Portal_Metadata_SOP.pdf"
  ,"metadata_template_path" => $_SERVER['DOCUMENT_ROOT']."/public_upload/library/template_files/metadata_template.docx"

  ,"sort_order" => 2
  ,"menu_hidden" => isset($_SESSION[$final_global_template_vars["session_key"]]) && $_SESSION[$final_global_template_vars["session_key"]] ? false : true
  ,"navbar_admin" => "/site/templates/admin_navbar_v1_update.php"
  ,"navbar" => "/site/templates/admin_navbar_update.php"
  ,"pages" => array(
    // array(
    //   "label" => "New Import"
    //   ,"path" => "/new"
    //   ,"display" => $apply_permissions("import", "submit_access")
    // ),
  )
  ,"upload_directory" => $_SERVER['DOCUMENT_ROOT'] . "/upload/sop_files/"
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
  ,"datatables" => array(
        //attributes of each datatable
        array(
            "dom_table_id" => "browse_table"
        ,"path_to_details_page" => "/"
        ,"path_to_sample_page" => isset($final_global_template_vars["path_to_this_module"])
            ? $final_global_template_vars["path_to_this_module"] . "/sample" : false
        ,"path_to_datatables_controller" => isset($final_global_template_vars["path_to_this_module"])
            ? $final_global_template_vars["path_to_this_module"] . "/datatables_browse_assays" : false
        ,"path_to_delete" => isset($final_global_template_vars["path_to_this_module"])
            ? $final_global_template_vars["path_to_this_module"] . "/assay/delete" : false
        ,"data" => "" // $assays_data_array
        ,"active_on_load" => true
        ,"fields" => array(
            "manage" => array("label" => "Options", "filter" => false, "show_column_toggle" => false, "initially_hidden" => false)
        ,"gene" => array("label" => "Gene", "comparison_default" => "equals", "filter" => false, "show_column_toggle" => false, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"peptide_sequence" => array("label" => "Sequence", "comparison_default" => "start_with", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")
        ,"peptide_start" => array("label" => "Peptide Start", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"peptide_end" => array("label" => "Peptide End", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"modification" => array("label" => "Modification", "comparison_default" => "equals", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"assay_type" => array("label" => "Assay Type", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")
        ,"matrix" => array("label" => "Matrix", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "int")
        ,"hydrophobicity" => array("label" => "Hydrophobicity", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
            // NEW COLUMNS
        ,"site_of_modification_protein" => array("label" => "Site of Modification - Protein", "comparison_default" => "equals", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"protein_species_label" => array("label" => "Species", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
            // ,"homology" => array("label" => "Homology", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"peptide_standard_purity" => array("label" => "Peptide Standard Purity", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"instrument" => array("label" => "Instrument", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"endogenous_detected" => array("label" => "Endogenous Detected", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"cptac_id" => array("label" => "CPTAC ID", "comparison_default" => "equals", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")
        )
        ,"search_fields" => array(
            "protein.gene_symbol"
        , "protein.uniprot_accession_id"
        , "analyte_peptide.peptide_sequence"
        , "analyte_peptide.modification_type"
        , "protein.cptac_id"
        )
        )
    )

  , "remove_side_nav" => true
);
