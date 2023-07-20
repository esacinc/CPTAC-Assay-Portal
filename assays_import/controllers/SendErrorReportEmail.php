<?php
namespace assays_import\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_import\models\AssaysImport;
use assays_manage\models\AssaysManage;
use assays\models\Assay;

class SendErrorReportEmail extends Controller {

    function send_error_report_email(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;


        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assays_manage = new AssaysManage($db_resource, $final_global_template_vars["session_key"]);
        $user = new \user_account\models\UserAccountDao($db_resource, $final_global_template_vars["session_key"]);

        $post = $request->getParams();

        // Get the panorama errors email recipients data.
        foreach ($final_global_template_vars["panorama_errors_email_recipients_ids"] as $account_id) {
            $admins[] = $user->get_user_account_info($account_id);
        }
        foreach ($admins as $admin) {
            // Email addresses array.
            $panorama_errors_email_recipients["addresses"][] = $admin["email"];
        }

        /*
         * Get all Panorama Import errors.
         */

        // // Get all sequences for a lab, to pass it to the next method, check_for_missed_images().
        // $all_sequences = $assay->getPeptideSequences( (int)$post["import_log_id"] );
        // // Get all of the problematic images.
        // $data["missed_images"] = $import_panorama_data->check_for_missed_images(
        //    (int)$post["laboratory_id"]
        //   ,(int)$post["import_log_id"]
        //   ,$all_sequences
        // );
        // // Get all of the problematic images data.
        // $data["missed_images_data"] = $import_panorama_data->check_for_missed_images_data( (int)$post["laboratory_id"], $all_sequences );

        // $data["response_curves_column_labels"] = array(
        //   "CPTAC ID"
        //   ,"Import Log ID"
        //   ,"Data Provider"
        //   ,"Curve Type"
        //   ,"Error Response"
        //   ,"Panorama URL"
        //   ,"Created Date"
        // );

        // // $fp = fopen($_SERVER["DOCUMENT_ROOT"].'/file.csv', 'w');
        // $fp = fopen('php://temp', 'w+');
        // fputcsv($fp, $data["response_curves_column_labels"]);

        // foreach ($data["missed_images"]["response_curves"] as $field) {
        //   $add_to_error["cptac_id"] = $field["cptac_id"];
        //   $add_to_error["import_log_id"] = (int)$post["import_log_id"];
        //   $add_to_error["laboratory_name"] = $field["laboratory_name"];
        //   foreach ($field["error"] as $error) {
        //     $error_data_merged = array_merge($add_to_error, $error);
        //     fputcsv($fp, $error_data_merged);
        //   }
        // }

        // // Place stream pointer at beginning.
        // rewind($fp);

        // // Create the attachment.
        // $csvData = stream_get_contents($fp);
        // $attachment = chunk_split( base64_encode($csvData) );

        // die('stop!');

        // Send email to the primary laboratory contact person.
        $reply_to_email = $_SESSION[$final_global_template_vars["session_key"]]["email"];
        $reply_to_name = $_SESSION[$final_global_template_vars["session_key"]]["givenname"] . ' ' . $_SESSION[$final_global_template_vars["session_key"]]["sn"];
        $laboratory_data = $assays_manage->get_laboratories((int)$post["laboratory_id"]);

        // Set the sent_to_email to the site admin's email address if we're on the development server (set in swpg_global_settings.php).
        $sent_to_email = ($final_global_template_vars["is_dev"])
            ? $final_global_template_vars["superadmin_email_address"] . ", " . $_SESSION[$final_global_template_vars["session_key"]]["email"]
            : implode(", ", $panorama_errors_email_recipients["addresses"]) . ", " . $_SESSION[$final_global_template_vars["session_key"]]["email"];

        // Get some record data so we can reference it in the email.
        $email_subject = "CPTAC Import: Panorama Error Report, " . $laboratory_data['laboratory_name'] . " (" . date('F j, Y h:i:s A') . ")";
        $headers = $final_global_template_vars['message_parts']['headers'];
        $headers .= 'From: CPTAC Assay Portal <noreply@' . $_SERVER["SERVER_NAME"] . '>' . "\r\n";
        $headers .= 'Reply-to: ' . $reply_to_name . ' <' . $reply_to_email . '>' . "\r\n";
        $headers .= 'Bcc: ' . $final_global_template_vars["superadmin_email_address"] . "\r\n";
        $body_message = '
    <h1>' . $email_subject . '</h1>' .
            $final_global_template_vars['message_parts']['body_connector']
            . '<p><strong>From:</strong> ' . $reply_to_name . '</p>
    <p><strong>Date/Time:</strong> ' . date('l, F jS, Y \a\t h:i:s A') . '</p>
    <p><strong>Details:</strong> <a href="https://' . $_SERVER["SERVER_NAME"] . '/assays_import/execute/?import_log_id=' . (int)$post["import_log_id"] . '">View the full error report</a></p>
  ';
        $message = $final_global_template_vars['message_parts']['body_header'] . $body_message . $final_global_template_vars['message_parts']['body_footer'];
        // // Add the CSV file attachment to the email.
        // $message .= "\r\n--".$final_global_template_vars['message_parts']['multipart_separator']."\r\n"
        //       . "Content-Type: text/csv\r\n"
        //       . "Content-Transfer-Encoding: base64\r\n"
        //       . "Content-Disposition: attachment; filename=\"file.csv\"\r\n"
        //       . "\r\n"
        //       . "$attachment\r\n"
        //       . "--".$final_global_template_vars['message_parts']['multipart_separator']."--";

        // Temporary, until I can figure out what to do about the class naming issue (assays).
        $additional_html = "<hr />
    <h2>Chromatogram Images</h2>

    <h2>Response Curve Images</h2>
    
    <h2>LOD/LOQ Data</h2>

    <h2>Curve Fit Data</h2>

    <h2>Repeatability Images</h2>

    <h2>Repeatability Data</h2>
  ";

        // Send the email.
        mail($sent_to_email, $email_subject, $message, $headers);

        return $response->withJson("email_sent");
    }

}