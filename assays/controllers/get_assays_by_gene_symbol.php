<?php
function get_assays_by_gene_symbol() {
    $app = \Slim\Slim::getInstance();
    $env = $app->environment();
    global $final_global_template_vars;
    require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
    $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
    $db_resource = $db_conn->get_resource();

    $assay = new Assay(
        $db_resource
        , $final_global_template_vars["session_key"]
    );

    $post = $app->request()->post();
    $data = false;

    if (isset($post['gene_symbol']) && !empty($post['gene_symbol'])) {
        $data = $assay->getApprovedGenesByGeneSymbol($post['gene_symbol']);
    }

    header("Content-type: text/json; charset=utf-8");

    echo json_encode($data);

}

?>