<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use public_upload\models\PublicUpload;
use assays\models\Assay;

use core\controllers\Controller;

class UploadSkylineFile extends Controller {
    function upload_skyline_file(Request $request, Response $response, $args = []) {
        //$app = \Slim\Slim::getInstance();
        global $final_global_template_vars;

        // Check permissions and accordingly set the 'no_permissions' variable (boolean)
        //$data["no_permissions"] = empty($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"]) ? true : false;
        //$data["page_title"] = "Upload Dashboard";

        
        $view = $this->container->get('view');
        $view->render($response,
            'upload_skyline_file.twig'
            , array(
                "page_title" => "Upload Skyline Files"
            )
        );
        return $response;
    }
}

?>
