<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use public_upload\models\PublicUpload;
use assays\models\Assay;

use core\controllers\Controller;

class InsertSopFile extends Controller {
    function insert_sop_file(Request $request, Response $response, $args = []) {

      global $final_global_template_vars;

      $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
      $db_resource = $db_conn->get_resource();
      $parsedBody = $request->getParsedBody();

      $import = new PublicUpload( $db_resource, $final_global_template_vars["session_key"] );

      $file_data = $parsedBody;

      $file_data['name'] = $parsedBody['file_data']['name'];
      $file_data['type'] = $parsedBody['file_data']['type'];
      $file_data['size'] = $parsedBody['file_data']['size'];

      $file_data['internal_file_name'] = $parsedBody['file_data']['internal_file_name'];

      $data = $import->insert_sop_file($file_data);


      return $response->withJson($data);
    }
}
