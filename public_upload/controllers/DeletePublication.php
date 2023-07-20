<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;


use public_upload\models\PublicUpload;
use assays\models\Assay;

use core\controllers\Controller;

class DeletePublication extends Controller {

function delete_publication(Request $request, Response $response, $args = []) {


  global $final_global_template_vars;

  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $import = new PublicUpload( $db_resource, $final_global_template_vars["session_key"] );

  $post = $request->getParsedBody();


  $data = $import->delete_publication( (int)$post['publication_id'] );

  return $response->withJson($data);
}
}
?>