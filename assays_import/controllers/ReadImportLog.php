<?php
/**
 * @desc Import Assays: controller for inserting and updating data
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

class ReadImportLog extends Controller {

    function read_import_log(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $import = new AssaysImport($db_resource, $final_global_template_vars["session_key"]);

        $today = date("Y-m-d");

        $import_log_id = $request->getParam("import_log_id");

        $current_module_location = $final_global_template_vars["absolute_path_to_this_module"];

        $import_log_text = $current_module_location . "/library/import_logs/" . $today . "/" . $import_log_id . ".txt";



        // Get all executed imports data.
        // add
        $data['executed_imports'] = $import->get_executed_imports($import_log_id);

        $data = [
            "import_in_progress" => $data['executed_imports'][0]['import_in_progress'],
            "import_log_text" => file_get_contents($import_log_text)

        ];

        //return $response->withHeader('Content-Type', 'text/plain')->write(file_get_contents($import_log_text));
        return $response->withJson($data);
    }

    function download_import_log(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $today = date("Y-m-d");

        $import_log_id = $request->getParam("import_log_id");

        $current_module_location = $final_global_template_vars["absolute_path_to_this_module"];

        $import_log_text = $current_module_location . "/library/import_logs/" . $today . "/" . $import_log_id . ".txt";


        if (file_exists($import_log_text)) {
            $fh = @fopen($import_log_text, 'r+');

            $stream = new \Slim\Http\Stream($fh);

            return $response->withHeader('Content-Type', 'application/force-download')
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Type', 'application/download')
                ->withHeader('Content-Type', 'text/plain')
                ->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Transfer-Encoding', 'binary')
                ->withHeader('Content-Disposition', 'attachment; filename=' . $import_log_id . ".txt")
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->withHeader('Pragma', 'public')
                ->withHeader('Content-Length', filesize($import_log_text))
                ->withBody($stream);
        }

        return $response;
    }

}