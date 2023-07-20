<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 10/14/18
 * Time: 10:01 PM
 */

namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use core\controllers\Controller;

class Statistics extends Controller {

    function statistics(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;
        require_once $final_global_template_vars["absolute_path_to_this_module"] . "/config/settings.php";
        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new Assay($db_resource, $final_global_template_vars["session_key"]);

        $assay_statistics = $assay->getStatisticsData();

        return $response->withHeader('Access-Control-Allow-Origin', '*')
                        ->withJson($assay_statistics[0]);
    }

}