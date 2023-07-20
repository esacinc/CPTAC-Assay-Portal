<?php
function import_kegg_data() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;

  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/kegg.class.php";
  require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/SWPG/models/XML2Array.php";
  $xml2array = new \swpg\models\XML2Array();
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $result = false;
  $kegg = new Kegg( $db_resource, $final_global_template_vars["session_key"] );

  // Truncate all tables
  $kegg->truncate_all_kegg_tables();

  //////////////////////////////////////////////////////////////////
  // Import all KEGG data and build out the closure table
  $heiarchy = array();
  $kegg_heiarchy = file_get_contents('/mnt/webrepo/fr-s-swpg-cpt-d/kegg_pathway_hierarchy.dat');
  $heiarchy = explode(PHP_EOL,$kegg_heiarchy);

  foreach($heiarchy as $heiarchy_value) {

    $ancestor = false;
    $descendant = false;
    $data_ancestor = array();
    $data_descendant = array();

    $single_heiarchy = explode("\t",$heiarchy_value);

    if( ( isset($single_heiarchy[0]) && !empty($single_heiarchy[0]) ) &&
        ( isset($single_heiarchy[1]) && !empty($single_heiarchy[1]) ) ) {

      // Setup ancestor data
      $data_ancestor["name"] = $single_heiarchy[0];
      $ancestor = $kegg->get_kegg_id( $data_ancestor["name"] );

      if(!empty($ancestor['kegg_id'])) {
        $ancestor = $kegg->get_kegg_record( $ancestor["kegg_id"] );
        $data_ancestor["kegg_parent"] = $ancestor["kegg_parent"];
      }

      // Insert/update
      $ancestor["kegg_id"] = $kegg->insert_update_kegg( $data_ancestor, $ancestor["kegg_id"] );
      
      // Set up descendant data
      $data_descendant["name"] = $single_heiarchy[1];
      $data_descendant["kegg_parent"] = $ancestor["kegg_id"];
      $descendant = $kegg->get_kegg_id( $data_descendant["name"] );

      // Insert/update
      $kegg->insert_update_kegg( $data_descendant, $descendant["kegg_id"] );
    }

  }

  //////////////////////////////////////////////////////////////////
  // Associate records with KEGG ids where applicable
  $kegg_ids = array();
  $kegg_ids_file = file_get_contents('/mnt/webrepo/fr-s-swpg-cpt-d/kegg_pathways.dat');
  $kegg_ids = explode(PHP_EOL,$kegg_ids_file);

  foreach($kegg_ids as $id_value) {
    if(!empty($id_value)) {
      $data_ids = array();
      $data_ids = explode("\t",$id_value);
      $kegg->insert_kegg_id( $data_ids );
    }
  }

  //////////////////////////////////////////////////////////////////
  // Import KEGG to UniProt mappings
  $kegg->import_kegg_uniprot_data( $final_global_template_vars["biodbnet_api_url"], $xml2array );

  $app->render(
    'import_kegg_data.php'
    ,array(
      "page_title" => "Import Kegg Data"
      ,"hide_side_nav" => true
      ,"result" => $result
    )
  );
}
?>