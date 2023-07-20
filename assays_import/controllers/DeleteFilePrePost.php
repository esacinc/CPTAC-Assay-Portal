<?php
/**
 * @desc Import Assays: controller for deleting SOP files pre-post, completely deleting
 * the records in the sop_files and sop_files_join tables.
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_import\models\AssaysImport;

class DeleteFilePrePost extends Controller {

    function delete_file_pre_post(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $import = new AssaysImport($db_resource, $final_global_template_vars["session_key"]);

        $file_id = (int)$request->getParam('file_id');

        $data = $import->delete_file_pre_post($file_id);

        return $response->withJson($data);
    }

}