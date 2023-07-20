<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;
use public_upload\models\PublicUpload;

class SubmitSkylineFile extends Controller {

    function submit_skyline_file(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;

        //$logger = $this->container->get('logger');

        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $import = new PublicUpload($db_resource, $final_global_template_vars["session_key"]);

        $session_key = $final_global_template_vars["session_key"];
        $session_id = $_SESSION[$session_key];
        $import_log_id = $_SESSION[$session_key]['import_log_id'];


        //$this->container->get('logger')->info($session_id["account_id"]);

        //pull account information to put together submission_id
        $account_details = $import->get_account_details($session_id["account_id"]);

        //assign values to concatenate into submission_id
        $submission_year = strval($account_details["submission_year"]);
        $first_name = $account_details["given_name"][0];
        $last_name = $account_details["sn"][0];
        $account_id = strval($account_details["account_id"]);
        $email = $account_details["email"];

        //get the count of the user imports by current user
        $user_import_count = $import->get_import_count();

        //format tht count+1 with leading zeros for submission_id
        $import_count = sprintf("%03d", count($user_import_count));

        //concatenate submission_id for insert into database
        $submission_id = $submission_year . $first_name . $last_name . $account_id . "_" . (string)$import_count;

        //update import_log table with submission_id
        $import->update_import_submission_id($_SESSION[$session_key]['import_log_id'], $submission_id, (int)$session_id["account_id"]);

        $import_log_submission_id = $import->get_submission_id($session_id["account_id"]);
        $last_updated_submission_id = $import_log_submission_id[0]['submission_id'];

        //remove import_log_id from session
        unset($_SESSION[$session_key]['import_log_id']);

        //send email to confirm submission
        //$this->submit_public_upload_send_mail($email, $submission_id);

        $view = $this->container->get('view');
        $view->render($response,
            'submit_skyline_file.twig'
            , array(
                "page_title" => "Submit Skyline Files"
            , "submission_id" => $submission_id
            )
        );


        return $response;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    function submit_public_upload_send_mail(string $user_email_address, string $submission_id) {
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
            "submission_id" => $submission_id,
            "site_url" => "https://{$site_url_server_host}/"
            ]
        );
    }

}
