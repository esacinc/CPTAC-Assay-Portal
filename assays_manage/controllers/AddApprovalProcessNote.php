<?php

namespace assays_manage\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_manage\models\AssaysManage;

class AddApprovalProcessNote extends Controller {

    function add_approval_process_note(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssaysManage($db_resource, $final_global_template_vars["session_key"]);

        $post = $request->getParams();
        $data = false;
        $data['email_sent'] = false;

        $this_protein_id = (int)$post['this_protein_id'];
        $all_protein_ids = $post['all_protein_ids'];
        $import_set_id = (int)$post['import_set_id'];
        $comment_text = $post['comment_text'];
        $apply_to_all = (int)$post['apply_to_all'];
        $send_email = (int)$post['send_email'];
        $user_id = (int)$_SESSION[$final_global_template_vars["session_key"]]["account_id"];
        $laboratory_id = (int)$post['laboratory_id'];

        // Insert the note into the database
        $data = $assay->add_approval_moderation_notes($this_protein_id, $all_protein_ids, $import_set_id, $user_id, $comment_text, $apply_to_all, $send_email);

        if ($data) {
            return $response->withJson($data);
        }
    }

}