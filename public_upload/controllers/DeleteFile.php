<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;


use public_upload\models\PublicUpload;
use assays\models\Assay;

use core\controllers\Controller;


class DeleteFile extends Controller
{
  function delete_file(Request $request, Response $response, $args = [])
  {

    global $final_global_template_vars;

    $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);

    $db_resource = $db_conn->get_resource();

    $import = new PublicUpload($db_resource, $final_global_template_vars["session_key"]);
    $data = $request->getParsedBody();
    

    $file_id = $data['file_id'];
    $data = $import->delete_file($file_id);

    //echo json_encode($data);
    return $response->withJson($data);
  }
}
?>