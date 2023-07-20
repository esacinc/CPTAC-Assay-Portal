<?php
namespace assays_preview\controllers;

use assays_preview\models\AssayPreview;
use Slim\Http\Request;
use Slim\Http\Response;

use assays_preview\models\AssaysPreview;
use user_account\models\AccountTypeEnum;
use user_account\models\UserRoleEnum;

use core\controllers\Controller;

class BrowseAssaysPreview extends Controller {
  function browse_assays_manage(Request $request, Response $response, $args = []) {

       global $final_global_template_vars;
       $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
       $db_resource = $db_conn->get_resource();
       $assay = new AssayPreview($db_resource, $final_global_template_vars["session_key"]);

       $data_array = array();

       // If user role is set to 'Universal Administrator', get all laboratories
       if (in_array(UserRoleEnum::UNI_ADMIN, $_SESSION[$final_global_template_vars["session_key"]]["user_role_list"])) {
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
           if ($assay->get_account_type($account_id) != AccountTypeEnum::LOCAL) {
               if (count($laboratories) == 1) {
                   $import_log = $assay->get_import_logs_by_lab_id($laboratories[0]["laboratories_id"]);
               } else {
                   $import_log = $assay->get_import_log();
               }
           }
       }

       // This server (for links to the public portal)
       $server_name = $_SERVER['SERVER_NAME'];
       $user_roles = $_SESSION[$final_global_template_vars["session_key"]]["user_role_list"];

       // Render
       $view = $this->container->get('view');
       $view->render($response, 'browse_assays_manage.twig', array(
           "page_title" => "Preview Assays"
         , "hide_side_nav" => true
         , "server_name" => $server_name
         , "laboratories" => $laboratories
         , "import_log" => $import_log
         , "user_role_list" => $user_roles
       ));
   }

}
