<?php
/**
 * @desc Import Assays: controller for deleting import data and related image files
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
namespace assays_import\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_import\models\AssaysImport;

class DeleteImport extends Controller {

    function delete_import(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $import = new AssaysImport($db_resource, $final_global_template_vars["session_key"]);

        $import_log_id = (int)$request->getParam('import_log_id');

        $data = $import->delete_import($import_log_id);

        return $response->withJson($data);
    }

}
