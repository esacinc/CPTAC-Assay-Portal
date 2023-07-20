<?php
function get_google_analytics_data() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/library/google-api-php-client/src/Google_Client.php";
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/library/google-api-php-client/src/contrib/Google_AnalyticsService.php";
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";


  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();

  $assay = new Assay($db_resource);
  $data = false;

  // Set up the connection
  $client = new Google_Client();
  $client->setApplicationName('Get Google Analytics Data');
  $client->setAssertionCredentials(
      new Google_AssertionCredentials(
          $final_global_template_vars["google_analytics_config"]["email_address"],
          array('https://www.googleapis.com/auth/analytics.readonly'),
          file_get_contents($final_global_template_vars["google_analytics_config"]["path_to_key_file"])
      )
  );
  $client->setClientId($final_global_template_vars["google_analytics_config"]["client_id"]);
  $client->setAccessType('offline_access');

  $analytics = new Google_AnalyticsService($client);

  // Get some data
  try {
    $results = $analytics->data_ga->get(
      $final_global_template_vars["google_analytics_config"]["id"]
      ,$final_global_template_vars["google_analytics_config"]["start_date"]
      ,$final_global_template_vars["google_analytics_config"]["end_date"]
      ,'ga:visitors');
    // Insert the total number of unique visitors into the database
    $assay->insert_google_analytics( $results['totalsForAllResults']['ga:visitors'] );

  } catch(Exception $e) {
    echo 'There was an error : - ' . $e->getMessage();
    die();
  }

  $app->render('get_google_analytics_data.php',array(
    "page_title" => "Get Google Analytics Data"
    ,"hide_side_nav" => true
    ,"data" => $results
    ,"menu" => $menu
  ));
}
?>