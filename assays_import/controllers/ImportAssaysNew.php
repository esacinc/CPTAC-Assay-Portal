<?php

namespace assays_import\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_import\models\AssaysImport;

class ImportAssaysNew extends Controller {

    function import_assays_new(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $data_array = array();

        // This server (for links to the public portal)
        $server_name = $_SERVER['SERVER_NAME'];

        // Render
        $view = $this->container->get('view');
        $view->render($response,
            'import_assays_new.twig',
            array(
                  "page_title" => "New Import"
                , "hide_side_nav" => true
                , "server_name" => $server_name
            ));
    }

}