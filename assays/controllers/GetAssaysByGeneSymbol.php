<?php

namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use core\controllers\Controller;

class GetAssaysByGeneSymbol extends Controller {

    function get_assays_by_gene_symbol(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;
        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $assay = new Assay(
            $db_resource
            , $final_global_template_vars["session_key"]
        );


        $data = false;
        $gene_symbol = $request->getParam('gene_symbol');

        if (isset($gene_symbol) && !empty($gene_symbol)) {
            $data = $assay->getApprovedGenesByGeneSymbol($gene_symbol);
        }

        return $response->withHeader('Content-Type', 'text/json; charset=utf-8')
                        ->withJson($data);
    }

}
