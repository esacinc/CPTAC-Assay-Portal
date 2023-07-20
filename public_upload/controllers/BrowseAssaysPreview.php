<?php
namespace public_upload\controllers;

use assays_preview\models\AssayPreview;
use Slim\Http\Request;
use Slim\Http\Response;

use assays_preview\models\AssaysPreview;
use user_account\models\AccountTypeEnum;
use user_account\models\UserRoleEnum;

use core\controllers\Controller;

class BrowseAssaysPreview extends Controller {

    function browse_assays_manage(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;
        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssayPreview($db_resource, $final_global_template_vars["session_key"]);

        $session_key = $final_global_template_vars["session_key"];
        $account_id = (int)$_SESSION[$session_key]["account_id"];
        $group_id = 8;

        $submission_ids = $assay->get_user_associated_submission($account_id, $group_id);

        // This server (for links to the public portal)
        $server_name = $_SERVER['SERVER_NAME'];

        // Render
        $view = $this->container->get('view');
        $view->render($response, 'browse_assays_preview.twig', array(
            "page_title" => "Preview Assays"
          , "hide_side_nav" => true
          , "server_name" => $server_name
          , "submission_ids" => $submission_ids
        ));

    }

}

