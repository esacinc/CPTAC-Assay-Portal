<?php
/**
 * @desc Import Assays: controller for processing file uploads using the jQuery File Upload Plugin
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 * jQuery File Upload Plugin PHP Example 5.7
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

namespace assays_import\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_import\models\AssaysImport;

class ProcessFileUpload extends Controller {

    function process_file_upload(Request $request, Response $response, $args = []) {
        $app = \Slim\Slim::getInstance();
        $env = $app->environment();
        global $final_global_template_vars;
        require_once($final_global_template_vars["absolute_path_to_this_module"] . "/models/upload.class.php");

        $upload_handler = new UploadHandler(
            array(
                "upload_dir" => $final_global_template_vars["upload_directory"]
            , "upload_url" => $final_global_template_vars["sop_file_upload_directory"]
            )
            , $final_global_template_vars["file_validation"]
        );

        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-Disposition: inline; filename="files.json"');
        header('X-Content-Type-Options: nosniff');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                break;
            case 'HEAD':
            case 'GET':
                $upload_handler->get();
                break;
            case 'POST':
                if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
                    $upload_handler->delete();
                } else {
                    $upload_handler->post();
                }
                break;
            case 'DELETE':
                $upload_handler->delete();
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
        }

    }

}