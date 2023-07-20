<?php
namespace assays_manage\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_manage\models\AssaysManage;

class EmailCsv extends Controller {

    function email_csv(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssaysManage($db_resource, $final_global_template_vars["session_key"]);

        $user_id = (int)$_SESSION[$final_global_template_vars["session_key"]]["account_id"];
        $laboratory_id = (int)$request->getParam('laboratory_id');
        $import_set_id = (int)$request->getParam('import_set_id');
        $email_csv_comment_to_lab = (int)$request->getParam('email_csv_comment_to_lab');
        $email_csv_comment_to_self = (int)$request->getParam('email_csv_comment_to_self');
        $message = $request->getParam('message');
        $sent = 'not sent';

        $message = !empty($message) ? $message : 'No message sent.';

        // Create the CSV file
        if ($laboratory_id && $import_set_id) {
            // Get the array for the CSV
            $notes = $assay->get_notes_by_import_set_id($import_set_id);

            if ($notes) {
                $path_to_temp_directory = $_SERVER['DOCUMENT_ROOT'] . '/swpg_files/cptac/temp/';
                $path_to_temp_directory_via_http = $_SERVER['DOCUMENT_ROOT'] . '/swpg_files/cptac/temp/';
                $filename = "CPTAC_" . date("YmdHis") . "_all_notes.csv";
                $fp = fopen($path_to_temp_directory . $filename, 'w');
                fputcsv($fp, array('gene_symbol', 'peptide_sequence', 'note_content', 'note_submitted_by'));
                foreach ($notes as $note) {
                    fputcsv($fp, $note);
                }
                fclose($fp);
            }

            // Set up the email

            $reply_to_email = $_SESSION[$final_global_template_vars["session_key"]]["email"];
            $reply_to_name = $_SESSION[$final_global_template_vars["session_key"]]["givenname"] . ' ' . $_SESSION[$final_global_template_vars["session_key"]]["sn"];
            $laboratory_data = $assay->get_laboratories($laboratory_id);

            // Set the sent_to_emails
            if ($email_csv_comment_to_lab && !$final_global_template_vars["is_dev"]) {
                // Set to the primary laboratory contact's email address if we're on the production server
                $sent_to_emails[] = $laboratory_data["primary_contact_email_address"];
            } else if ($email_csv_comment_to_lab && $final_global_template_vars["is_dev"]) {
                // Set to the site admin's email address if we're on the development server (set in swpg_global_settings.php)
                $sent_to_emails[] = $final_global_template_vars["superadmin_email_address"];
            } else {
                $sent_to_emails = false;
            }
            // Set to the currently logged in user's email address
            if ($email_csv_comment_to_self)
                $sent_to_emails[] = $_SESSION[$final_global_template_vars["session_key"]]["email"];

            if ($sent_to_emails) {

                // Get some record data so we can reference it in the email
                $email_subject = 'CPTAC Qualification Note: CSV Report';
                $headers = $final_global_template_vars['message_parts']['headers'];
                $headers .= 'From: CPTAC Assay Portal <noreply@' . $_SERVER["SERVER_NAME"] . '>' . "\r\n";
                $headers .= 'Reply-to: ' . $reply_to_name . ' <' . $reply_to_email . '>' . "\r\n";
                $headers .= 'Bcc: ' . $reply_to_email . ', ' . $final_global_template_vars["superadmin_email_address"] . "\r\n";
                $body_message = '
        <h1>' . $email_subject . '</h1>' .
                    $final_global_template_vars['message_parts']['body_connector']
                    . '<p><strong>From:</strong> ' . $reply_to_name . '</p>
        <p><strong>Date/Time:</strong> ' . date('l, F jS, Y \a\t h:i:s A') . '</p>
        <hr />
        <p><strong>Download CSV:</strong> <a href="http://' . $_SERVER["SERVER_NAME"] . $path_to_temp_directory_via_http . $filename . '">' . $filename . '</a></p>
        <p><strong>Additional Message</strong>:<br />
        ' . nl2br($message) . '</p>
      ';
                $message = $final_global_template_vars['message_parts']['body_header'] . $body_message . $final_global_template_vars['message_parts']['body_footer'];

                // Send the email
                mail(implode(',', $sent_to_emails), $email_subject, $message, $headers);

                $sent = 'sent';

            }

        }

        return $response->withJson($sent);
    }

}