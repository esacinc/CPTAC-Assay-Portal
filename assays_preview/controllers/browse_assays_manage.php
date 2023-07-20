<?php
function browse_assays_manage() {
    $LOCAL_ACCOUNT_TYPE = 3;
    $app = \Slim\Slim::getInstance();
    global $final_global_template_vars;
    require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
    $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
    $db_resource = $db_conn->get_resource();
    $assay = new Assay($db_resource, $final_global_template_vars["session_key"]);

    $data_array = array();

    // If user role is set to 'Universal Administrator', get all laboratories
    if (in_array('4', $_SESSION[$final_global_template_vars["session_key"]]["user_role_list"])) {
        $laboratories = $assay->get_laboratories();
    } // Get all laboratories this user is associated to
    else {
        $laboratories = $assay->get_user_associated_laboratories((int)$_SESSION[$final_global_template_vars["session_key"]]["account_id"]);
    }

    $account_id = (int)$_SESSION[$final_global_template_vars["session_key"]]["account_id"];
    foreach ($laboratories as $value) {
        $import_log_temp = $assay->get_user_associated_imports($account_id, $value["laboratories_id"]);
        if (empty($import_log)) {
            $import_log = $import_log_temp;
        } else {
            $import_log = array_merge($import_log, $import_log_temp);
        }
    }

    // Get the import log dates
    if (empty($import_log)) {
        if ($assay->get_account_type($account_id) != $LOCAL_ACCOUNT_TYPE) {
            if (count($laboratories) == 1) {
                $import_log = $assay->get_import_logs_by_lab_id($laboratories[0]["laboratories_id"]);
            } else {
                $import_log = $assay->get_import_log();
            }
        }
    }

    // This server (for links to the public portal)
    $server_name = $_SERVER['SERVER_NAME'];


    // Render
    $app->render('browse_assays_manage.php', array(
        "page_title" => "Preview Assays"
    , "hide_side_nav" => true
    , "server_name" => $server_name
    , "laboratories" => $laboratories
    , "import_log" => $import_log
    ));
}

?>