<?php
/**
 * @desc Import Assays: controller for downloading SOP files
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

class DownloadFile extends Controller {

    function download_file(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $import = new AssaysImport($db_resource, $final_global_template_vars["session_key"]);

        $file_id = (int)$request->getParam('file_id');

        $import->download_file($final_global_template_vars["upload_directory"], $file_id);
    }

}