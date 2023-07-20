<?php
/* Note that you are able to use any key that exists in
 * the global settings, and it will overwrite it
 */

// Set $_SERVER variables for the scripts running via the command line
$_SERVER["CORE_TYPE"] = (isset($_SERVER["CORE_TYPE"]) && ($_SERVER["CORE_TYPE"] != NULL)) ? $_SERVER["CORE_TYPE"] : 'core';
$_SERVER["SERVER_NAME"] = (isset($_SERVER["SERVER_NAME"]) && ($_SERVER["SERVER_NAME"] != NULL)) ? $_SERVER["SERVER_NAME"] : 'cptacdev.cancer.gov';

$panorama_paths = array(
  "server_raw" => "https://panoramaweb.org"
  ,"server" => "https://panoramaweb.org/labkey"
  ,"query_path" => "/query/CPTAC%20Assay%20Portal/"
  ,"targetedms_query_path" => "/targetedms/CPTAC%20Assay%20Portal/"
  ,"project_query_path" => "/project/CPTAC%20Assay%20Portal/"
);

$swpg_module_settings = array(
  "module_name" => "CPTAC Assay Portal"
  ,"module_description" => "Browse and find assays."
  ,"module_icon_css_classes" => "fa fa-fw fa-home"
  ,"sort_order" => 20
  ,"menu_hidden" => true
  ,"menu_template_name" => ""
  ,"pages" => array(
    array(
      "label" => "Browse Assays", "path" => "/", "display" => false
    )
  )
  ,"navbar" => "/site/templates/navbar.php"
  ,"public_navbar" => "/site/templates/public_navbar.php"
  ,"public_navbar_home" => "/site/templates/public_navbar_home.php"
  ,"public_layout_template_name" => "swpg_bootstrap_admin_non_responsive_public.twig"
  ,"demo_home_page_layout_template_name" => "swpg_bootstrap_admin_non_responsive_public_demo.twig"
  ,"datatables" => array(
    // Attributes of each datatable
    array(
      "dom_table_id" => "browse_table"
      ,"path_to_details_page" => "/"
      ,"path_to_sample_page" => isset($final_global_template_vars["path_to_this_module"])
          ? $final_global_template_vars["path_to_this_module"] . "/sample" : false
      ,"path_to_datatables_controller" => isset($final_global_template_vars["path_to_this_module"])
          ? $final_global_template_vars["path_to_this_module"] . "/datatables_browse_assays" : false
      ,"path_to_delete" => isset($final_global_template_vars["path_to_this_module"])
          ? $final_global_template_vars["path_to_this_module"] . "/assay/delete" : false
      ,"data" => ""
      ,"active_on_load" => true
      ,"fields" => array(
        "manage" => array("label" => "", "filter" => false, "show_column_toggle" => false, "initially_hidden" => true)
        ,"gene" => array("label" => "Gene", "comparison_default" => "equals", "filter" => false, "show_column_toggle" => false, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"peptide_sequence" => array("label" => "Proteins and peptides with assays", "comparison_default" => "start_with", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")
        ,"laboratory_name" => array("label" => "Submitting Laboratory", "comparison_default" => "start_with", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")
        ,"peptide_start" => array("label" => "Peptide Start", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"peptide_end" => array("label" => "Peptide End", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"modification" => array("label" => "Modification", "comparison_default" => "equals", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")
        ,"assay_type" => array("label" => "Assay Type", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")
        ,"matrix" => array("label" => "Matrix", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "int")
        // ,"lod" => array("label" => "LOD", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        // ,"lloq" => array("label" => "LLOQ", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"hydrophobicity" => array("label" => "Hydrophobicity", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        // NEW COLUMNS
        ,"site_of_modification_protein" => array("label" => "Site of Modification - Protein", "comparison_default" => "equals", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"protein_species_label" => array("label" => "Species", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        // ,"homology" => array("label" => "Homology", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"peptide_standard_purity" => array("label" => "Peptide Standard Purity", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"instrument" => array("label" => "Instrument", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"endogenous_detected" => array("label" => "Endogenous Detected", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"med_total_CV" => array("label" => "Med Total CV", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => true, "filter_type" => "text", "data_type" => "text")
        ,"cptac_id" => array("label" => "CPTAC ID", "comparison_default" => "equals", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")
        ,"uniprot_protein_name" => array("label" => "Protein Name", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")
        ,"uniprot_gene_synonym" => array("label" => "Gene Alias", "comparison_default" => "contains", "filter" => false, "show_column_toggle" => true, "initially_hidden" => false, "filter_type" => "text", "data_type" => "text")

    )
      ,"search_fields" => array(
        "protein.gene_symbol"
        , "group.name"
        , "protein.cptac_id"
        , "protein.uniprot_protein_name"
        , "protein.uniprot_gene_synonym"
        , "analyte_peptide.modification_type"
        , "protein.uniprot_accession_id"
        , "analyte_peptide.peptide_sequence"
        , "analyte_peptide.peptide_start"
        , "analyte_peptide.peptide_end"
      )

      ,"uniprot_fields" => array(
          "uniprot_accession"
        , "cptac_id"
      )

    )
  )
  // For importing data from UniProt
  ,"uniprot_regions_array" => array(
    'topological domain'
    ,'transmembrane region'
    ,'intramembrane region'
    ,'domain'
    ,'repeat'
    ,'calcium binding'
    ,'zinc finger'
    ,'dna binding'
    ,'nucleotide phosphate-binding region'
    ,'region of interest'
    ,'coiled coil'
    ,'motif'
    ,'compositional bias'
  )
  ,"files_directory"=> "/assays/library/images/"
  //,"panorama_images_storage_path"=> $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/panorama_test/"
  //,"panorama_images_path"=> "/swpg_files/cptac/panorama_test/"
  //,"sop_files_path" => "/swpg_files/cptac/sop_files/"
  //,"disqus_shortname" => ($_SERVER["SERVER_NAME"] == 'assaysdev.cancer.gov') ? 'cptacdevswpg' : 'cptacswpg'
  // Panorama / Labkey settings
  ,"labkey_config" => array(
     "email" => "kristen.nyce@esacinc.com"
    ,"password" => "ESACINC1801"
    ,"server_raw" => $panorama_paths["server_raw"]
    ,"server" => $panorama_paths["server"]
    ,"query_endpoint_live" => $panorama_paths["server"].$panorama_paths["query_path"]
    ,"targetedms_query_path" => $panorama_paths["server"].$panorama_paths["targetedms_query_path"]
    ,"project_endpoint_live" => $panorama_paths["server"].$panorama_paths["project_query_path"]
  )

  ,"uniprot_protein_api_url" => "http://www.uniprot.org/uniprot/"
  ,"entrez_api_url" => "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/"
  ,"panorama_url" => "https://panoramaweb.org/"
  ,"biodbnet_api_url" => "http://biodbnet.abcc.ncifcrf.gov/webServices/rest.php/biodbnetRestApi.json"
);
