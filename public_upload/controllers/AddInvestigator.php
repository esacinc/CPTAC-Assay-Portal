<?php

namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;


use public_upload\models\PublicUpload;
use assays\models\Assay;

use core\controllers\Controller;

class AddInvestigator extends Controller {

    function add_investigator(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $import = new PublicUpload($db_resource, $final_global_template_vars["session_key"]);

        $post = $request->getParsedBody();

        $data = $import->add_investigator($post['investigator_name']);

        return $response->withJson($data);

    }

}

?>