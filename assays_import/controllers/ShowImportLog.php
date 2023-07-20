<?php
/**
 * @desc Import Assays: controller for inserting and updating data
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
namespace assays_import\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use \GuzzleHttp\Client;

use core\controllers\Controller;

use assays_import\models\AssaysImport;
use assays_import\models\UserAccountImport;
use assays_import\models\ImportPanoramaData;
use assays\models\Assay;


class ShowImportLog extends Controller {

    function show_import_log(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new Assay($db_resource);

        $data = array();
        $get = $request->getParams();

        $data["laboratory_data"] = $assay->get_laboratory_by_import_log_id($get["import_log_id"]);

        $data["import_executed_status"] = (isset($get["import_executed_status"]) && ($get["import_executed_status"] == "true")) ? true : false;
        $data["session"] = $_SESSION[$final_global_template_vars["session_key"]];


        // Get the user's roles.
        $user_role_ids = isset($data["session"]["user_role_list"])
            ? $data["session"]["user_role_list"] : array();

        // Get the laboratory name for the page title (superadmin only).
        $laboratory_name = in_array(4, $user_role_ids) ? ": " . $data["laboratory_data"]["laboratory_name"] : "";

        // Render
        $view = $this->container->get('view');
        $view->render($response,
            "show_import_log.twig"
            , array(
                "page_title" => "Show Import Log" . $laboratory_name
            , "hide_side_nav" => true
            , "data" => $data
            , "show_log" => $data["import_executed_status"]
            , "log_cache_id" => uniqid()
            )
        );
    }

}