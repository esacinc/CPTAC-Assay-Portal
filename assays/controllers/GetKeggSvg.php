<?php
namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use assays\models\Kegg;
use core\controllers\Controller;

class GetKeggSvg extends Controller {

    function get_kegg_svg(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;
        require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/SWPG/models/XML2Array.php";
        $xml2array = new \swpg\models\XML2Array();
        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $kegg = new Kegg( $db_resource, $final_global_template_vars["session_key"] );

        $assay = new Assay(
            $db_resource
            , $final_global_template_vars["session_key"]
        );

        $data = false;

        $kegg_id = $request->getParam('kegg_id');

        if (isset($kegg_id) && !empty($kegg_id)) {
            $kegg_id = (int)$kegg_id;

            $kegg = $kegg->get_real_kegg_id($kegg_id);

            $file_name = $_SERVER['PATH_TO_DATA'] . '/assay_portal/svg/hsa' . $kegg["real_kegg_id"] . ".svg";

            $data = file_get_contents($file_name);

            $response->write($data);
            return $response->withHeader('Content-Type', 'application/text');
        }
    }

}
