<?php
function get_kegg_svg() {
    $app = \Slim\Slim::getInstance();
    $env = $app->environment();
    global $final_global_template_vars;
    require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
    require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/kegg.class.php";
    require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/SWPG/models/XML2Array.php";
    $xml2array = new \swpg\models\XML2Array();
    $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
    $db_resource = $db_conn->get_resource();

    $kegg = new Kegg( $db_resource, $final_global_template_vars["session_key"] );

    $assay = new Assay(
        $db_resource
        , $final_global_template_vars["session_key"]
    );

    $post = $app->request()->post();
    $data = false;

    if (isset($post['kegg_id']) && !empty($post['kegg_id'])) {
        $kegg_id = (int)$post['kegg_id'];

        $kegg = $kegg->get_real_kegg_id($kegg_id);

        $file_name = $_SERVER['PATH_TO_DATA'] . '/assay_portal/svg/hsa' . $kegg["real_kegg_id"] . ".svg";

        write_log("", $file_name);

        $data = file_get_contents($file_name);

    }

    header("Content-type: text/xml; charset=utf-8");

    echo $data;

}

?>