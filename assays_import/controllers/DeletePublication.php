<?php
/**
 * @desc Import Assays: controller for deleting SOP files (set to is_deleted = 1 in the database)
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
class DeletePublication extends Controller {

    function delete_publication(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $import = new AssaysImport($db_resource, $final_global_template_vars["session_key"]);

        $publication_id = (int)$request->getParam('publication_id');

        $data = $import->delete_publication($publication_id);

        return $response->withJson($data);
    }

}