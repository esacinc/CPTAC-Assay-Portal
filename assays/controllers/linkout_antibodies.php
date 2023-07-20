<?php
function linkout_antibodies() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/linkout.class.php";
  $db_conn_antibodies = new \swpg\models\db( $final_global_template_vars["db_connection_antibodies"] );
  $domimplementation = new DOMImplementation;
  $db_resource = $db_conn_antibodies->get_resource();
  // Instantiate the Linkout class
  $linkout = new Linkout(
    $db_resource
    ,$final_global_template_vars["session_key"]
  );

  $mail_body = "";
  $mail_body_failed_connection = "";
  $headers = 'From: Antibody Portal <noreply@'.$_SERVER["SERVER_NAME"].'>' . "\r\n";

  // Get the array for the CSV
  $linkout_antibodies = $linkout->get_linkout_antibodies();

  $i = 1;
  foreach ($linkout_antibodies as $linkout_antibody) {

    // Process the dash-separated cptc_name field.
    $cptc_name = "";
    $cptc_name_array = ($linkout_antibody["cptc_name"] != NULL) ? explode("-",$linkout_antibody["cptc_name"]) : false;

    // Process the space-separated cptc_name field.
    if( count(explode(" ",$cptc_name_array[1])) > 1 ) {
      $cptc_name = explode(" ",$cptc_name_array[1]);
      $cptc_name = $cptc_name[0];
    } else {
      $cptc_name = $cptc_name_array[1];
    }

    // Process the comma-separated keywords_abbreviations field.
    $keywords_abbreviations_array = ($linkout_antibody["keywords_abbreviations"] != NULL)
        ? explode(";",$linkout_antibody["keywords_abbreviations"]) : false;

    $keywords_abbreviations_array_trimmed = array();
    if($keywords_abbreviations_array) {
      // Remove whitespace.
      foreach($keywords_abbreviations_array as $kw) {
        $keywords_abbreviations_array_trimmed[] = trim($kw);
      }

      // If $keywords_abbreviations_array_trimmed returns true, 
      // Loop through, and build the string for the LinkOut system.
      $keywords_abbreviations = "";
      if($keywords_abbreviations_array_trimmed) {
        foreach($keywords_abbreviations_array_trimmed as $keyword) {
          if(!empty($keyword) && ($keyword != $cptc_name)) {
            $keywords_abbreviations .= "OR \"".$keyword."\"[tiab] ";
          }
        }
      }
    }

    // Build the XML.
    // Creates a DOMDocumentType instance.
    $dtd = $domimplementation->createDocumentType(
        'LinkSet'
      , '-//NLM//DTD LinkOut 1.0//EN'
      , 'http://www.ncbi.nlm.nih.gov/projects/linkout/doc/LinkOut.dtd'
    );

    // How to add the "ENTITY"? No clue... looked around for a long time, no go.
    // Probably not really neeeded. Just ended up putting the URL base inline within the "Base" element.
    // "[<!ENTITY base.url "http://antibodies.cancer.gov">]"

    // Creates a DOMDocument instance.
    $domtree = $domimplementation->createDocument(null, "", $dtd);

    // Set other properties.
    $domtree->formatOutput = true;
    // $domtree->encoding = 'UTF-8';
    // $domtree->standalone = false;

    $linkSet = $domtree->createElement("LinkSet");
    $linkSet = $domtree->appendChild($linkSet);

    $link = $domtree->createElement("Link");
    $link = $linkSet->appendChild($link);

    $link->appendChild($domtree->createElement("LinkId",$i));
        $link->appendChild($domtree->createElement(
        "ProviderId"
      , $final_global_template_vars["linkout_antibodies_ftp"]["linkout_provider_id"])
    );

    $objectSelector = $domtree->createElement("ObjectSelector");
    $objectSelector = $link->appendChild($objectSelector);

    $objectSelector->appendChild($domtree->createElement("Database","PubMed"));

    $objectList = $domtree->createElement("ObjectList");
    $objectList = $objectSelector->appendChild($objectList);

    $objectList->appendChild($domtree->createElement(
        "Query"
      , "\"".$cptc_name."\"[tiab] OR \"".$linkout_antibody["name"]."\"[tiab] ".
        trim($keywords_abbreviations)." OR \"".$cptc_name." protein, human\" [Supplementary Concept]"
    ));

    $objectUrl = $domtree->createElement("ObjectUrl");
    $objectUrl = $link->appendChild($objectUrl);

    $objectUrl->appendChild($domtree->createElement("Base",$final_global_template_vars["linkout_antibodies_ftp"]["target_url"]));
    $objectUrl->appendChild($domtree->createElement("Rule",'/apps/site/detail/'.$linkout_antibody["url"]));

    // Save the file.
    $filename = $cptc_name."_resource.xml";
    $domtree->save( $final_global_template_vars["temp_directory_path"].$filename );

    $file_location = $final_global_template_vars["temp_directory_path"].$filename;

    /* FTP the file to LinkOut. */

    // Set up basic connection.
    $conn_id = ftp_connect( $final_global_template_vars["linkout_antibodies_ftp"]["host"] ); 

    // Login with the username and password.
    $login_result = ftp_login(
        $conn_id
      , $final_global_template_vars["linkout_antibodies_ftp"]["username"]
      , $final_global_template_vars["linkout_antibodies_ftp"]["password"]
    );

    // Check the connection.
    $connection_error = ((!$conn_id) || (!$login_result)) ? true : false;

    // If there are no connection errors, proceed with file upload.
    // Otherwise, kill the executuion of the script, and send an email to the site admin.
    if(!$connection_error) {
      // Turn passive mode on.
      ftp_pasv($conn_id, true);
      $upload = false;
      $upload = ftp_put($conn_id, "/holdings/".$filename, $file_location, FTP_ASCII);

      // Check the upload status.
      $upload_error = false;
      // If there's an upload error, add to the email body, and set $upload_error to true.
      if (!$upload) {
        $mail_body .= $i.") FTP upload has failed for '".$filename."'!\n\n";
        $upload_error = true;
      }

      // Close the FTP stream. 
      ftp_close($conn_id);

      $i++;
      // if($i > 10) break;

    } else {
      // We had a connection error. Send an email to the super admin and kill the script.
      $mail_body_failed_connection .= "The FTP connection has failed while trying to transfer the '".$filename."' file.\n\n";
      $mail_body_failed_connection .= "Attempted to connect to - ".$final_global_template_vars["linkout_antibodies_ftp"]["host"]." - for user - ".$final_global_template_vars["linkout_antibodies_ftp"]["username"]."\n\n-----\n\n";
      $mail_body_failed_connection .= "Timestamp: ".date('l, F jS, Y - h:i:s A')."\n\n";
      $mail_body_failed_connection .= "Server: ".$_SERVER["SERVER_NAME"]."\n\n";
      $mail_body_failed_connection .= "IP Address: ".$_SERVER["SERVER_ADDR"]."\n\n";
      // Send the email.
      mail(
          $final_global_template_vars["superadmin_email_address"]
        , "LinkOut Antibodies: Failed Connecting to FTP Server"
        , $mail_body_failed_connection
        , $headers
      );
      // Kill it.
      die();
    }
    
  }

  // FTP script executed and completed. Send an email to the super admin, along with errors, if generated.
  $append = $upload_error ? "... but, with errors" : "";
  $mail_body .= "Transfer of Antibody XML LinkOut files has been executed and completed".$append.".\n\n-----\n\n";
  $mail_body .= "Timestamp: ".date('l, F jS, Y - h:i:s A')."\n\n";
  $mail_body .= "Server: ".$_SERVER["SERVER_NAME"]."\n\n";
  $mail_body .= "IP Address: ".$_SERVER["SERVER_ADDR"]."\n\n";
  // Send the email.
  mail(
      $final_global_template_vars["superadmin_email_address"]
    , "LinkOut Antibodies: FTP Transfer Complete"
    , $mail_body
    , $headers
  );

}
?>