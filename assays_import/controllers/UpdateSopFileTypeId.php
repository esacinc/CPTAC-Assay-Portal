<?php
/**
 * @desc Import Assays: controller for updating the SOP file type id in the sop_files database table.
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

class UpdateSopFileTypeId extends Controller {

    function update_sop_file_type_id(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;
        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $import = new AssaysImport($db_resource, $final_global_template_vars["session_key"]);

        $data = $import->update_sop_file_type_id($request->getParam());

        return $response->withJson($data);
    }

}