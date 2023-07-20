<?php
/**
 * @desc Import Assays: controller for inserting and updating data
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
function execute_import( $import_log_id = false ) {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["swpg_module_list"]["assays"]["absolute_path_to_this_module"]."/models/assays.class.php";
  require_once $final_global_template_vars["absolute_path_to_this_module"]."/models/assays_import.class.php";
  require_once $final_global_template_vars["absolute_path_to_this_module"]."/models/user_account_import.class.php";
  require_once $final_global_template_vars["swpg_module_list"]["assays"]["absolute_path_to_this_module"]."/models/import_panorama_data.class.php";
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $assay = new Assay( $db_resource );
  $import = new AssaysImport( $db_resource, $final_global_template_vars["session_key"] );
  $user_account_import = new UserAccountImport($db_resource, $final_global_template_vars["session_key"]);
  $import_panorama_data = new ImportPanoramaData( $db_resource );
  $user = new \user_account\models\UserAccountDao( $db_resource, $final_global_template_vars["session_key"] );

  $data = array();
  $laboratory_data = array();
  $get = $app->request->get();
  $post = $app->request->post();
  $data["import_executed_status"] = (isset($get["import_executed_status"]) && ($get["import_executed_status"] == "true") ) ? true : false;
  $data["session"] = $_SESSION[$final_global_template_vars["session_key"]];

  $user_laboratory_ids = $data["session"]["associated_groups"];

  
  // Get the user's roles.
  $user_role_ids = isset($data["session"]["user_role_list"]) 
    ? $data["session"]["user_role_list"] : array();

  if(!empty($get)) {
    // Get the laboratory metadata via the import_log_id GET variable.
    $data["laboratory_data"] = $assay->get_laboratory_by_import_log_id( $get["import_log_id"] );

    // If get_laboratories() returns false, throw a 404.
    if(!$data["laboratory_data"]) $app->notFound();

    // If user is not a superadmin or not in a laboratory (group), throw a 404.
    if( !in_array(4, $user_role_ids) && !in_array($data["laboratory_data"]["laboratory_id"], $user_laboratory_ids) ) {
      $app->notFound();
    }

    // Get all executed imports data.
    $data["executed_imports"] = $import->get_executed_imports( $data["laboratory_data"]["import_log_id"] );

    // Get all sequences for a lab, to pass it to the next method, check_for_missed_images().
    $all_sequences = $assay->getPeptideSequences( $data["laboratory_data"]["import_log_id"] );

    // Get all of the problematic images.
    $data["missed_images"] = $import_panorama_data->check_for_missed_images( 
       $data["laboratory_data"]["laboratory_id"]
      ,$data["laboratory_data"]["import_log_id"]
      ,$all_sequences 
    );

    // Get all of the problematic images data.
    $data["missed_images_data"] = $import_panorama_data->check_for_missed_images_data(
       $data["laboratory_data"]["laboratory_id"]
      ,$data["laboratory_data"]["import_log_id"]
      ,$all_sequences
    );

    $data["deleted"] = (isset($get["deleted"]) && ($get["deleted"] == "true")) ? true : false;
    $data["reset"] = (isset($get["reset"]) && ($get["reset"] == "true")) ? true : false;
  


    // look for import logs
    $data['path_to_log'] = false;
    $folder_path = $_SERVER['DOCUMENT_ROOT'].$final_global_template_vars['import_log_location'];
    $folders =  scandir ($folder_path,2);
    $ignore = array('.','..');
    foreach($folders as $key => $value)
    {
      if(!in_array($value,$ignore))
      {
        
        $log_file = $folder_path.'/'.$value.'/'.$data["laboratory_data"]["import_log_id"].'.txt';
        if(is_file($log_file))
        {
          $data['path_to_log'] = $final_global_template_vars['import_log_location'].'/'.$value.'/'.$data["laboratory_data"]["import_log_id"].'.txt';
          break;    
        }      
      }
    }


  }

  /* 
   * Import From Panorama Into Portal Tables:
   *
   * protein
   * analyte_peptide
   * uniprot_splice_junctions
   * uniprot_snps
   * uniprot_isoforms
   *
   */

	

  if(!empty($post)) {

    clear_log($post["import_log_id"]);
    write_log($post["import_log_id"],'Import Started. ID:'.$post['import_log_id']);

    // Get the laboratory metadata via the import_log_id POST variable.
    $data["laboratory_data"] = $assay->get_laboratory_by_import_log_id( $post["import_log_id"] );
    // If get_laboratory_by_import_log_id() returns false, throw a 404.
    if(!$data["laboratory_data"]) $app->notFound();
    // Set the run_missed_images variable.
    $test_import = (isset($post["test_import"]) && ($post["test_import"] == "true")) 
      ? "&test_import=1" : false;
    // Set the run_missed_images variable.
    $run_missed_images = (isset($post["run_missed_images"]) && ($post["run_missed_images"] == "true")) 
      ? "&run_missed_images=true" : false;

    if( !$run_missed_images ) {
			
      // Execute the full import script.
      $url = "https://".$_SERVER["SERVER_NAME"].$final_global_template_vars["path_to_this_module"]
      ."/import_panorama_protein_peptide/?import_log_id=".$data["laboratory_data"]["import_log_id"]
      ."&account_id=".$post["account_id"].$test_import."&uniquehash=".uniqid();
      
      $import_type = ($test_import) ? "Test" : "Full";
      write_log($post["import_log_id"],"Execute the {$import_type} import script");

      //die($url);	

			$ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 15);
      curl_exec( $ch );
      curl_close( $ch );
      
      if ($import_type == "Full") {
      	$user_account_import->add_user_account_import($data["laboratory_data"]["import_log_id"]);
      }
    } else {
      // Execute the import for missed images script.
      $url = "https://".$_SERVER["SERVER_NAME"].$final_global_template_vars["path_to_this_module"]
      ."/import_panorama_data/?import_log_id=".$data["laboratory_data"]["import_log_id"]
      ."&imports_executed_log_id=".$post["imports_executed_log_id"]
      .$run_missed_images."&account_id=".$post["account_id"]."&uniquehash=".uniqid();
     
      write_log($post["import_log_id"],'Execute the import for missed images script');

		  $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 15);
      curl_exec( $ch );
      curl_close( $ch );
    }

    $data["import_executed_status"] = true;
  }

  // Get the panorama errors email recipients data.
  foreach($final_global_template_vars["panorama_errors_email_recipients_ids"] as $account_id) {
    $admins[] = $user->get_user_account_info( $account_id );
  }
  foreach ($admins as $admin) {
    // Names array.
    $data["panorama_errors_email_recipients"]["names"][] = $admin["given_name"]." ".$admin["sn"];
  }

  // Get the laboratory name for the page title (superadmin only).
  $laboratory_name = in_array(4, $user_role_ids) ? ": ".$data["laboratory_data"]["laboratory_name"] : "";

  // Render
  $app->render(
    "execute_import.php"
    ,array(
      "page_title" => "Execute and Manage Import".$laboratory_name
      ,"hide_side_nav" => true
      ,"data" => $data
      ,"show_log" => $data["import_executed_status"]
      ,"log_cache_id" => uniqid()
    )
  );
}
?>