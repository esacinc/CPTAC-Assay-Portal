<?php

namespace assays_manage\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_manage\models\AssaysManage;

class ApprovalProcess extends Controller {

    function approval_process(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssaysManage($db_resource, $final_global_template_vars["session_key"]);

        $post = $request->getParams();
        $updated_ids = array();

        if (!empty($post)) {
            if (!empty($post['ids_approved'])) {
                $ids = json_decode($post['ids_approved']);
                foreach ($ids as $id) {
                    $updated_ids[] = $assay->approval_process((int)$id, true);
                }
            }

            if (!empty($post['ids_disapproved'])) {
                $ids = json_decode($post['ids_disapproved']);
                foreach ($ids as $id) {
                    $updated_ids[] = $assay->approval_process((int)$id, false);
                }
            }

            if (!empty($post['ids_approved_345'])) {
                $ids = json_decode($post['ids_approved_345']);
                foreach ($ids as $id) {
                    $updated_ids[] = $assay->additional_approval_process((int)$id, true);
                }
            }

            if (!empty($post['ids_disapproved_345'])) {
                $ids = json_decode($post['ids_disapproved_345']);
                foreach ($ids as $id) {
                    $updated_ids[] = $assay->additional_approval_process((int)$id, false);
                }
            }
        }
        return $response->withJson($updated_ids);
    }

}