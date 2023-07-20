<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;
use public_upload\models\PublicUpload;

class SubmitAssays extends Controller {

    function submit_assays(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;

        $logger = $this->container->get('logger');



        $view = $this->container->get('view');
        $view->render($response,
            'submit_assays.twig'
            , array(
                "page_title" => "Submit Assays"
            )
        );


        return $response;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    function submit_public_upload_send_mail(string $user_email_address) {
        global $final_global_template_vars;

        $site_url_server_host = $_SERVER["SERVER_NAME"];

        if (((int)$_SERVER["SERVER_PORT"]) !== 443) {
            $site_url_server_host .= ":" . $_SERVER["SERVER_PORT"];
        }

        $mailer = new \core\models\Mail\Mailer($this->container->get('view'),
            $final_global_template_vars["swpg_module_list"]["public_upload"]["absolute_path_to_this_module"] . "/templates");

        $mailer->sendSMTPMail("public_upload_mail.twig",
            $final_global_template_vars["superadmin_email_address"],
            $user_email_address,
            $final_global_template_vars["superadmin_email_address"],
            ($final_global_template_vars["site_name"] . ": Public Upload"),
            [],
            [
            "admin_email_address" => $final_global_template_vars["superadmin_email_address"],
            "site_url" => "https://{$site_url_server_host}/"
            ]
        );
    }

}