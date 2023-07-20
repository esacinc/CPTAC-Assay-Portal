<?php
function choose_laboratory(){
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["swpg_module_list"]["assays_preview"]["absolute_path_to_this_module"] . "/models/assays.class.php";
  //require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/GUMP/gump.class.php";
  $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
  $db_resource = $db_conn->get_resource();
  $group = new \group\models\GroupDao( $db_resource, $final_global_template_vars["session_key"] );
  $assay = new Assay( $db_resource, $final_global_template_vars["session_key"] );

  $data = false;
  $post = $app->request()->post();
  $laboratories = $assay->get_laboratories();

  if(!empty($post)) {

    $gump = new GUMP();
    $rules = array(
      "labs" => "required|integer|min_numeric,1"
    );
    $validated = $gump->validate($app->request()->post(), $rules);

    $errors = array();
    if($validated !== TRUE){
      $errors = \swpg\models\utility::gump_parse_errors($validated);
    }

    if(!$errors) {

      $laboratory_data = $assay->get_laboratories( (int)$post["labs"] );
      // Set the sent_to_email to the site admin's email address if we're on the development server (set in swpg_global_settings.php)
      $send_to_email = ($final_global_template_vars["is_dev"])
        ? $final_global_template_vars["superadmin_email_address"]
        : $_SESSION[$final_global_template_vars["session_key"]]["email"];
      // Or not! Send to both, regardless!
      $send_to_email = $final_global_template_vars["superadmin_email_address"].",".$_SESSION[$final_global_template_vars["session_key"]]["email"];

      $full_name = $_SESSION[$final_global_template_vars["session_key"]]["givenname"].' '.$_SESSION[$final_global_template_vars["session_key"]]["sn"];

      // Get some record data so we can reference it in the email
      $email_subject = $final_global_template_vars["site_name"].': Registration - Laboratory Selection';
      $headers = $final_global_template_vars['message_parts']['headers'];
      $headers .= 'From: '.$final_global_template_vars["site_name"].' <noreply@'.$_SERVER["SERVER_NAME"].'>' . "\r\n";
      $headers .= 'Bcc: '.$final_global_template_vars["superadmin_email_address"]."\r\n";
      $body_message = '
        <h1>'.$email_subject.'</h1>'.
        $final_global_template_vars['message_parts']['body_connector']
        .'<p>Hello '.$_SESSION[$final_global_template_vars["session_key"]]["givenname"].',
        <p>We have received your request and will get back to you shortly.</p>
        <p><strong>Name:</strong> '.$full_name.'</p>
        <p><strong>Email:</strong> '.$_SESSION[$final_global_template_vars["session_key"]]["email"].'</p>
        <p><strong>Date/Time:</strong> '.date('l, F jS, Y \a\t h:i:s A').'</p>
        <p><strong>Chosen Laboratory:</strong> '.$laboratory_data["laboratory_name"].' (id: '.$laboratory_data["laboratories_id"].')</p>
      ';
      $message = $final_global_template_vars['message_parts']['body_header'].$body_message.$final_global_template_vars['message_parts']['body_footer'];

      // Send the email
      mail($send_to_email, $email_subject, $message, $headers);

      $app->flash('success', 'The laboratory request has been sent to the administrator. You will be receiving an email shortly.');
      $app->redirect($final_global_template_vars["path_to_this_module"].'/choose_laboratory');
    } else {
      // $env = $app->environment();
      $env["swpg_validation_errors"] = $errors;
    }

  }

  $app->render(
      "choose_laboratory.php"
    , array(
        "page_title" => "Choose a Laboratory"
      , "laboratories" => $laboratories
      , "givenname" => $_SESSION[$final_global_template_vars["session_key"]]["givenname"]
      , "errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
    )
  ); 
}
?>