<?php

namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

use public_upload\models\PublicUpload;
use public_upload\models\Assay;

use core\controllers\Controller;

class InsertMetadata extends Controller {

    function insert_metadata(Request $request, Response $response, $args = []) {
      

        global $final_global_template_vars;


        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);

        $db_resource = $db_conn->get_resource();
        $session_key = $final_global_template_vars["session_key"];
        $import = new PublicUpload($db_resource, $session_key);

        $assay = new Assay($db_resource);

        $data = array();
        $current_values = array();
        //$laboratory_name = "Washington University";
        $session_key = $final_global_template_vars["session_key"];
        $session_id = $_SESSION[$session_key];
        $data["session"] = $_SESSION[$session_key];

        $getParam = $request->getParsedBody();


        if ($getParam['postParam']) {

            $current_values = $getParam['postParam'];

            $data['sop_files'] = $import->get_sop_files($current_values['import_log_id']);
            $data['uploaded_files'] = isset($current_values['uploaded_files'])
                ? $import->get_sop_files_by_id($current_values['uploaded_files']) : false;

            $data['publications'] = $import->get_publications($current_values['import_log_id']);
            // Posted Publications
            if (isset($current_values['import_log_id']) && isset($current_values["publication_citation"])) {
                for ($i = 0; $i < count($current_values["publication_citation"]); $i++) {
                    $data['submitted_publications'][] = array(
                        "publication_citation" => $current_values["publication_citation"][$i]
                    , "publication_url" => $current_values["publication_url"][$i]
                    );
                }
            }

        } elseif ($args['assay_import_id']) {

            $import_log_id = $args['assay_import_id'];

            $current_values = $import->get_assay_import_record($import_log_id);

            $data['sop_files'] = $import->get_sop_files($import_log_id);
            $data['publications'] = $import->get_publications($import_log_id);
            $data['primary_investigators'] = $import->get_primary_investigators($import_log_id);

            // Get the user's roles.
            $user_role_ids = isset($data["session"]["user_role_list"])
                ? $data["session"]["user_role_list"] : array();
            // Get the laboratory metadata via the import_log_id GET variable.
            $data["laboratory_data"] = $assay->get_laboratory_by_import_log_id($import_log_id);
            // Get the laboratory name for the page title (superadmin only).
            $laboratory_name = in_array(4, $user_role_ids) ? ": " . $data["laboratory_data"]["laboratory_name"] : "";

        } elseif ($_SESSION[$session_key]["import_log_id"]) {
            $import_log_id = $_SESSION[$session_key]["import_log_id"];

            $current_values = $import->get_assay_import_record($import_log_id);

            $data['sop_files'] = $import->get_sop_files($import_log_id);
            $data['publications'] = $import->get_publications($import_log_id);
            $data['primary_investigators'] = $import->get_primary_investigators($import_log_id);

            // Get the user's roles.
            $user_role_ids = isset($data["session"]["user_role_list"])
                ? $data["session"]["user_role_list"] : array();
            // Get the laboratory metadata via the import_log_id GET variable.
            $data["laboratory_data"] = $assay->get_laboratory_by_import_log_id($import_log_id);
            // Get the laboratory name for the page title (superadmin only).
            $laboratory_name = in_array(4, $user_role_ids) ? ": " . $data["laboratory_data"]["laboratory_name"] : "";

        } else {
          //  $import_log_id = $import->get_recent_import_id($session_id["account_id"]);
            if($import_log_id) {
                $data['primary_investigators'] = $import->get_primary_investigators($import_log_id);
                $current_values = $import->get_assay_import_record($import_log_id);
            } else {

              $data['primary_investigators'] = $import->get_primary_investigators(105);
              $current_values = $import->get_assay_import_record(105);
            }
        }

        // Get all SOP File types.
        $data['sop_file_types'] = $import->get_sop_file_types();

        // Get assay types
        $data["assay_types"] = $import->get_assay_types();

        // Get peptide_standard_purity_types
        $data['peptide_standard_purity_options'] = $import->get_peptide_standard_purity_types();

        //$data = array_merge($current_values, $data);

        $view = $this->container->get('view');
        $view->render($response,
            'insert_metadata.twig'
            , array(
                "page_title" => "Add Import Metadata" . $laboratory_name
            , "hide_side_nav" => true
            , "data" => $data
            , "errors" => isset($this->container['swpg_validation_errors'])
                    ? $this->container['swpg_validation_errors'] : false
            ));

        return $response;

    }
}
