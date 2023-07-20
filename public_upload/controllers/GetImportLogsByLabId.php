<?php

namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

use public_upload\models\PublicUpload;
use public_upload\models\Assay;

use core\controllers\Controller;

class GetImportLogsByLabId extends Controller {

    function get_import_logs_by_lab_id(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;
        require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";

        //$this->container->get('logger')->info("inside get import logs by lab id");

        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new Assay($db_resource, $final_global_template_vars["session_key"]);

        $post = $request->getParsedBody();
        $data = false;

        if (isset($post['laboratory_id']) && !empty($post['laboratory_id'])) {
            $laboratory_id = (int)$post['laboratory_id'];
            //$this->container->get('logger')->info($laboratory_id);
            $data = $assay->get_import_logs_by_lab_id($laboratory_id);

        }


        return $response->withJson($data);
    }
}

?>