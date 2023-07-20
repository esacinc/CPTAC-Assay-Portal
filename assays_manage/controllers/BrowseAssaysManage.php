<?php

namespace assays_manage\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_manage\models\AssaysManage;

class BrowseAssaysManage extends Controller {

    function browse_assays_manage(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssaysManage($db_resource, $final_global_template_vars["session_key"]);

        $get["laboratory_id"] = (int)$request->getParam("laboratory_id");
        $get["import_log_id"] = (int)$request->getParam("import_log_id");

        $data_array = array();

        // Get all laboratories
        $laboratories = $assay->get_laboratories();
        // Get the import log
        $import_log = $assay->get_import_log();
        // This server (for links to the public portal)
        $server_name = $_SERVER['SERVER_NAME'];

        // Render
        $view = $this->container->get('view');
        $view->render($response, 'browse_assays_manage.twig', array(
            "page_title" => "Manage Assays"
        , "hide_side_nav" => true
        , "server_name" => $server_name
        , "laboratories" => $laboratories
        , "import_log" => $import_log
        , "get" => $get
        ));
    }

}
