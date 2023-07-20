<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use public_upload\models\PublicUpload;
use assays\models\Assay;

use core\controllers\Controller;

class UpdateSopFileTypeId extends Controller {

function update_sop_file_type_id(Request $request, Response $response, $args = []) {

  global $final_global_template_vars;
  
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  
  $data = $request->getParsedBody();

  $import = new PublicUpload( $db_resource, $final_global_template_vars["session_key"] );

  $data = $import->update_sop_file_type_id($data);

  return $response->withJson($data);
}
}
