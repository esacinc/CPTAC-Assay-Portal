<?php
/* Note that you are able to use any key that exists in
 * the global settings, and it will overwrite it
 */
$swpg_module_settings = array(
  "module_name" => "Preview Assays"
  ,"module_description" => "Preview imported assays on the portal."
  ,"module_icon_css_classes" => "fa fa-fw fa-eye"
  ,"sort_order" => 3
  ,"menu_hidden" => isset($_SESSION[$swpg_global_settings["session_key"]]) && $_SESSION[$swpg_global_settings["session_key"]] ? false : true
  ,"navbar" => "/site/templates/admin_navbar_v1_update.php"
  //,"navbar" => "/site/templates/admin_navbar.php"
  ,"layout_template_name" => "swpg_bootstrap_admin_non_responsive_v1.twig"
  //,"layout_template_name" => "swpg_bootstrap_admin_non_responsive.twig"
  ,"pages" => array(
    array(
      "label" => "Browse and Preview Assays"
      ,"path" => "/"
      ,"display" => $apply_permissions("assay_preview", "browse_access")
    )
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

?>
