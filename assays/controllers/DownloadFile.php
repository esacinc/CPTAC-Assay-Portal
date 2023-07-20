<?php
namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use core\controllers\Controller;

class DownloadFile extends Controller {

    function download_file(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;
        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $assay = new Assay(
            $db_resource
            , $final_global_template_vars["session_key"]
        );

        $sop_files_id = (int)$request->getQueryParam('sop_files_id');

        //$this->container->get('logger')->info("sop file id " . $sop_files_id);

        $data = $assay->download_file($sop_files_id);

        if ($data['file_type'] == 'application/pdf' || $data['file_type'] == 'application/vnd.openxmlformats-officedocument.word' || $data['file_type'] == 'application/vnd.openxmlformats-officedocument.spre') {
            if(file_exists($final_global_template_vars["sop_files_path"] . $data['internal_file_name'])) {
                $fh = @fopen($final_global_template_vars["sop_files_path"] . $data['internal_file_name'], 'r');

                $stream = new \Slim\Http\Stream($fh);

                return $response->withHeader('Content-Type', 'application/force-download')
                    ->withHeader('Content-Type', 'application/octet-stream')
                    ->withHeader('Content-Type', 'application/download')
                    ->withHeader('Content-Type', $data['file_type'])
                    ->withHeader('Content-Description', 'File Transfer')
                    ->withHeader('Content-Transfer-Encoding', 'binary')
                    ->withHeader('Content-Disposition', 'attachment; filename=' . $data['file_name'])
                    ->withHeader('Expires', '0')
                    ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                    ->withHeader('Pragma', 'public')
                    ->withHeader('Content-Length', filesize($final_global_template_vars["sop_files_path"] . $data['internal_file_name']))
                    ->withBody  ($stream);
            }
        }

        return $response;
    }

}
