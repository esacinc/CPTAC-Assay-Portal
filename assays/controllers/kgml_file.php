<?php
function kgml_file() {

    $app = \Slim\Slim::getInstance();
    global $final_global_template_vars;
    require_once($final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php");
    $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
    $db_resource = $db_conn->get_resource();

    $assay = new Assay(
        $db_resource
        , $final_global_template_vars["session_key"]
    );

    $kgml_id = $app->request()->get("kgml_id");

    $file_name = $_SERVER['PATH_TO_DATA'] . '/assay_portal/kgml/' . $kgml_id . ".xml";

    echo $file_name;

    header("Content-type: text/xml; charset=utf-8");

    echo file_get_contents($file_name);

}