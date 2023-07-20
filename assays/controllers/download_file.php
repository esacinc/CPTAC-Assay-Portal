<?php
function download_file() {
  $app = \Slim\Slim::getInstance();
  global $final_global_template_vars;
  require_once( $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php" );
  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();

  $assay = new Assay(
    $db_resource
    ,$final_global_template_vars["session_key"]
  );

  $sop_files_id = (int)$app->request()->get('sop_files_id');
  $data = $assay->download_file( $sop_files_id );

  if($data['file_type'] == 'application/pdf' || $data['file_type'] == 'application/vnd.openxmlformats-officedocument.word' || $data['file_type'] == 'application/vnd.openxmlformats-officedocument.spre')
  {
    $file_to_upload = array('file_contents'=>$final_global_template_vars["sop_files_path"].$data['internal_file_name']);
    $ext = explode('.',$data['internal_file_name']);
    $ext = $ext[count($ext)-1];
    $header_output_vars["filename"] = $data['file_name'].'.'.$ext;
    $header_output_vars["file"] = file_get_contents($final_global_template_vars["sop_files_path"].$data['internal_file_name']);
    $header_output_vars["content_type"] = $data['file_type'];
    
    header("Pragma: public");
    header("Cache-Control: private",false);
    header("Content-type: {$header_output_vars['content_type']}");
    header("Content-Disposition: attachment; filename=" . $header_output_vars['filename']);  
    echo $header_output_vars["file"]; //change content-disposition to inline so that they can view it on the page

    exit;
   }
   else
   {
      echo '<h1>Download file Error, please contact support.</h1>';
   }

}
?>
