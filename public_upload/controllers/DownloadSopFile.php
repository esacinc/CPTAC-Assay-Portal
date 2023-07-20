<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;


use public_upload\models\PublicUpload;
use assays\models\Assay;

use core\controllers\Controller;


class DownloadSopFile extends Controller {
  function download_sop_file(Request $request, Response $response, $args = []) {

      global $final_global_template_vars;
      $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
      $db_resource = $db_conn->get_resource();

      $import = new PublicUpload($db_resource, $final_global_template_vars["session_key"]);


      $file_id = $args['file_id'];

      $filename = $import->download_file($final_global_template_vars["sop_file_upload_directory"], $file_id);

    

      if(file_exists($filename)) {
          $fh = @fopen($filename, 'r+');

          $stream = new \Slim\Http\Stream($fh);

          return $response->withHeader('Content-Type', 'application/force-download')
              ->withHeader('Content-Type', 'application/octet-stream')
              ->withHeader('Content-Type', 'application/download')
              ->withHeader('Content-Type', $data['file_type'])
              ->withHeader('Content-Description', 'File Transfer')
              ->withHeader('Content-Transfer-Encoding', 'binary')
              ->withHeader('Content-Disposition', 'attachment; filename=' . $filename)
              ->withHeader('Expires', '0')
              ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
              ->withHeader('Pragma', 'public')
              ->withHeader('Content-Length', filesize($filename))
              ->withBody  ($stream);
      }

      return $response;
  }
}
?>
