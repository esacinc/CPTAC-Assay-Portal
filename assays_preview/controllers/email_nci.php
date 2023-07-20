<?php
function email_nci() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["swpg_module_list"]["assays_manage"]["absolute_path_to_this_module"]."/models/assays.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $assay = new AssaysManage( $db_resource, $final_global_template_vars["session_key"] );
  $user = new \user_account\models\UserAccountDao( $db_resource, $final_global_template_vars["session_key"] );

  $post = $app->request()->post();
  $sent = false;

  $user_id = (int)$_SESSION[$final_global_template_vars["session_key"]]["account_id"];
  // $account_id = (int)$app->request()->post('account_id');
  // $import_log_id = (int)$app->request()->post('import_log_id');
  $laboratory_id = (int)$app->request()->post('laboratory_id');
  $message = $app->request()->post('message');
  $sent = 'not sent';

  $message = !empty($message) ? $message : 'No additional message sent.';

  // Test from Gor. Please disregard.

  if($laboratory_id) {
    // Set up the email
    $reply_to_email = $_SESSION[$final_global_template_vars["session_key"]]["email"];
    $reply_to_name = $_SESSION[$final_global_template_vars["session_key"]]["givenname"].' '.$_SESSION[$final_global_template_vars["session_key"]]["sn"];

    // Get the laboratory name
    $laboratory_data = $assay->get_laboratories( $laboratory_id );

    // Email recipients.
    $emails = array();
    $intended_recipients_text = "";
    // Set the site admin email recipients.
    $emails[] = $final_global_template_vars["superadmin_email_address"];
    // Get the NCI email recipients.
    $nci_user_emails = $user->get_user_account_emails_by_group_id( 7 );
    foreach($nci_user_emails as $single_email) {
      // If not on the development server, add NCI email recipients to the $emails array.
      if( !$final_global_template_vars["is_dev"] ) {
        $emails[] = $single_email["email"];
      } else {
        // If on the development server, just include a comma-delimited list of intended email recipients.
        $intended_recipients[] = $single_email["email"];
        $intended_recipients_text = "<p><strong>Intended Recipients:</strong> ".implode(', ',$intended_recipients)."</p>";
      }
    }

    if($emails) {

      // Get some record data so we can reference it in the email
      $email_subject = 'CPTAC Assay Import: Ready For Review';
      $headers = $final_global_template_vars['message_parts']['headers'];
      $headers .= 'From: CPTAC Assay Portal <noreply@'.$_SERVER["SERVER_NAME"].'>' . "\r\n";
      $headers .= 'Reply-to: '.$reply_to_name.' <'.$reply_to_email.'>' . "\r\n";
      $headers .= 'Bcc: '.$reply_to_email.', '.$final_global_template_vars["superadmin_email_address"]."\r\n";
      $body_message = '
        <h1>'.$email_subject.'</h1>'.
        $final_global_template_vars['message_parts']['body_connector']
        .'<p><strong>From:</strong> '.$reply_to_name.'</p>
        <p><strong>Date/Time:</strong> '.date('l, F jS, Y \a\t h:i:s A').'</p>
        <hr />
        <p><strong>Laboratory:</strong> '.$laboratory_data["laboratory_name"].'</p>
        <p><strong>Additional Message</strong>:<br />
        '.nl2br($message).'</p>
        <p><a href="https://'.$_SERVER["SERVER_NAME"].'/assays_manage/">Review the imported assays</a></p>
        '.$intended_recipients_text;
      $message = $final_global_template_vars['message_parts']['body_header'].$body_message.$final_global_template_vars['message_parts']['body_footer'];

      // Send the email
      mail( implode(', ',$emails), $email_subject, $message, $headers );

      $sent = 'sent';

    }

  }
  
  echo json_encode($sent);
}
?>