<?php

namespace assays_manage\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_manage\models\AssaysManage;

class DeleteNote extends Controller {

    function delete_note(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssaysManage($db_resource, $final_global_template_vars["session_key"]);

        $user_id = (int)$_SESSION[$final_global_template_vars["session_key"]]["account_id"];
        $note_id = (int)$request->getParam('note_id');

        $data = $assay->delete_note($note_id);

        if ($data) {
            return $response->withJson($data);
        }
    }

}
