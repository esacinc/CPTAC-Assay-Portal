<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

use public_upload\models\PublicUpload;
use public_upload\models\UploadHandler;
use assays\models\Assay;

use core\controllers\Controller;


class ProcessFileUpload extends Controller {

  function process_file_upload(Request $request, Response $response, $args = []) {

    global $final_global_template_vars;


    //generate unique file name
    $fileName = time().'_'.basename($_FILES["file"]["name"]);


    //file upload path
    $targetDir = $final_global_template_vars["sop_file_upload_directory"];
    $targetFilePath = $targetDir . $fileName;


    //allow certain file formats
    $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
    $allowTypes = array('pdf','doc','docx');

    $data = array();
    $data['file'] = $_FILES["file"];
    $data['file']['internal_file_name'] = $fileName;

    if(in_array($fileType, $allowTypes)){
      //upload file to server
      if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){
        //insert file data into the database if needed
        //........
        //$data['status'] = 'ok';
        return $response->withJson($data);
      }else{
        //$data['status'] = 'err';
        //$this->container->get('logger')->info("move file error");
      }
    } else {
     //$data['status'] = 'type_err';
    }


   }
}
