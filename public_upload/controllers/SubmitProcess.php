<?php

namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_manage\models\AssaysManage;
use assays_manage\models\AssayApprovalStatusEnum;

class SubmitProcess extends Controller {

    function submit_process(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssaysManage($db_resource, $final_global_template_vars["session_key"]);

        $post = $request->getParams();

        if (!empty($post)) {
            if (!empty($post['submitted_id'])) {
                $submitted_id = $post['submitted_id'];
                $status = $post['status'];

                if($status == "Submit") {
                    $updated_id[] = $assay->submit_process((int)$submitted_id, AssayApprovalStatusEnum::AWAITING_APPROVAL);
                } else {
                    $updated_id[] = $assay->submit_process((int)$submitted_id, AssayApprovalStatusEnum::WITHDRAWN);
                }
            }

        }
        return $response->withJson($updated_id);
    }

}