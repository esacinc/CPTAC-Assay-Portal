<?php
function add_approval_process_note() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $assay = new Assay( $db_resource, $final_global_template_vars["session_key"] );

  $post = $app->request()->post();
  $data = false;
  $data['email_sent'] = false;

  $this_protein_id = (int)$app->request()->post('this_protein_id');
  $all_protein_ids = $app->request()->post('all_protein_ids');
  $import_set_id = (int)$app->request()->post('import_set_id');
  $comment_text = $app->request()->post('comment_text');
  $apply_to_all = (int)$app->request()->post('apply_to_all');
  $send_email = (int)$app->request()->post('send_email');
  $user_id = (int)$_SESSION[$final_global_template_vars["session_key"]]["account_id"];
  $laboratory_id = (int)$app->request()->post('laboratory_id');

  // Insert the note into the database
  $data = $assay->add_approval_moderation_notes(
      $this_protein_id
    , $all_protein_ids
    , $import_set_id
    , $user_id
    , $comment_text
    , $apply_to_all
    , $send_email 
  );

  // Send email to the primary laboratory contact person
  if($send_email) {

    $reply_to_email = $_SESSION[$final_global_template_vars["session_key"]]["email"];
    $reply_to_name = $_SESSION[$final_global_template_vars["session_key"]]["givenname"].' '.$_SESSION[$final_global_template_vars["session_key"]]["sn"];
    $laboratory_data = $assay->get_laboratories( $laboratory_id );

    // Set the sent_to_email to the site admin's email address if we're on the development server (set in swpg_global_settings.php)
    $sent_to_email = ($final_global_template_vars["is_dev"])
      ? $final_global_template_vars["superadmin_email_address"]
      : $laboratory_data["primary_contact_email_address"];
    $sent_to_name = $laboratory_data["primary_contact_name"];

    // Get some record data so we can reference it in the email
    $record_data = $assay->get_record_data( $this_protein_id );
    $email_subject = 'CPTAC Qualification Note: '.$record_data["gene_symbol"].' - '.$record_data["peptide_sequence"];
    $headers = $final_global_template_vars['message_parts']['headers'];
    $headers .= 'From: CPTAC Assay Portal <noreply@'.$_SERVER["SERVER_NAME"].'>' . "\r\n";
    $headers .= 'Reply-to: '.$reply_to_name.' <'.$reply_to_email.'>' . "\r\n";
    $headers .= 'Bcc: '.$reply_to_email.', '.$final_global_template_vars["superadmin_email_address"]."\r\n";
    $body_message = '
      <h1>'.$email_subject.'</h1>'.
      $final_global_template_vars['message_parts']['body_connector']
      .'<p><strong>From:</strong> '.$reply_to_name.'</p>
      <p><strong>To:</strong> '.$sent_to_name.'</p>
      <p><strong>Date/Time:</strong> '.date('l, F jS, Y \a\t h:i:s A').'</p>
      <hr />
      <p><strong>Gene Symbol:</strong> '.$record_data["gene_symbol"].'</p>
      <p><strong>Peptide Sequence:</strong> '.$record_data["peptide_sequence"].'</p>
      <p><strong>Notes</strong><br /> '.nl2br($comment_text).'</p>
    ';
    $message = $final_global_template_vars['message_parts']['body_header'].$body_message.$final_global_template_vars['message_parts']['body_footer'];

    // Send the email
    mail($sent_to_email, $email_subject, $message, $headers);

    $data['email_sent'] = true;
  }
  
  if($data) {
    echo json_encode($data);
  }
}
?>