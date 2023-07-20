<?php
/**
 * @desc Import chromosome_number, chromosome_start, and chromosome_end from Entrez Gene into CPTAC's Assay Portal database
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
function import_entrez_genomic_context() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;

  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/import_entrez_genomic_context.class.php";
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
  require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/SWPG/models/XML2Array.php";
  $xml2array = new \swpg\models\XML2Array();
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $result = false;

  $assay = new Assay( $db_resource, $final_global_template_vars["session_key"] );
  $import = new ImportEntrezGenomicContext( $db_resource ,$final_global_template_vars["session_key"] );

  $genes = $assay->get_all_genes();

  foreach($genes as $gene) {
    $data["entrez_gene_data"] = $import->import_entrez_gene_data(
      $gene['gene_symbol']
      ,$final_global_template_vars["entrez_api_url"]
      ,$xml2array
    );
    //sleep(1);
    sleep(5);	
  }

  $app->render(
    'import_entrez_genomic_context.php'
    ,array(
      "page_title" => "Import Entrez Genomic Context"
      ,"hide_side_nav" => true
      ,"result" => $result
    )
  );
}
?>
