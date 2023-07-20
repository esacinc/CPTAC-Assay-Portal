<?php

namespace assays_manage\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_manage\models\AssaysManage;

class GetNotes extends Controller {

    function get_notes(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssaysManage($db_resource, $final_global_template_vars["session_key"]);

        $post = $request->getParams();
        $data = false;

        if (isset($post['protein_id']) && !empty($post['protein_id'])) {
            $protein_id = (int)$post['protein_id'];
            $data = $assay->get_notes($protein_id);
        }

        return $response->withJson($data);
    }

}