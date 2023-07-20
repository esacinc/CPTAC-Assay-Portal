<?php
function export_csv() {
  $app = \Slim\Slim::getInstance();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/config/settings.php";
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
  $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
  $db_resource = $db_conn->get_resource();
  $assay = new Assay($db_resource,$final_global_template_vars["session_key"]);
  $data = false;

  $side_bar_filter = array();
  $dropdown_filter = array();

  $sortable_key_fields = array_keys($final_global_template_vars['datatables'][0]['fields']);
  $posted_data = json_decode($app->request()->post('csv_filter'),true);

  $side_bar_filter = $posted_data['sidebar_filter'];
  $dropdown_filter = $posted_data['dropdown_filter'];
  $search_filter = $posted_data['search_string'];

  $assay_data = $assay->export_csv($side_bar_filter,$dropdown_filter,$search_filter);
  $fields = $final_global_template_vars['datatables'][0]['fields'];


  $labels = array();
  foreach($fields as $field){
     array_push($labels,$field["label"]);
  }
  $date_info = getdate();
  $filename = "CPTAC_Assays_export_".$date_info[year]."-".$date_info[mon]."-".$date_info[mday]."-".$date_info[hours]."-".$date_info[minutes];

  header("Content-Type: text/csv");
  header("Content-Disposition: attachment; filename=".$filename.".csv");

  $output = fopen("php://output", "w");
  fputcsv($output,$labels);
  foreach ($assay_data['aaData'] as $row){
    fputcsv($output, $row);
  }
  fclose($output);
  exit;
  }
?>