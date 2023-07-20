<?php
function import_assays_new() {
  $app = \Slim\Slim::getInstance();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays_import.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  //$assay = new AssaysManage( $db_resource, $final_global_template_vars["session_key"] );

  //$get["laboratory_id"] = (int)$app->request->get("laboratory_id");
  //$get["import_log_id"] = (int)$app->request->get("import_log_id");

  $data_array = array();

  // Get all laboratories
  //$laboratories = $assay->get_laboratories();
  // Get the import log
  //$import_log = $assay->get_import_log();
  // This server (for links to the public portal)
  $server_name = $_SERVER['SERVER_NAME'];

  // Render
  $app->render('import_assays_new.php',array(
    "page_title" => "New Import"
    ,"hide_side_nav" => true
    ,"server_name" => $server_name
    //,"laboratories" => $laboratories
    //,"import_log" => $import_log
    //,"get" => $get
  ));
}
?>