<?php
/**
 * @desc Import Assays: controller for inserting and updating data
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

namespace assays_import\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_import\models\AssaysImport;
use assays\models\Assay;

class InsertUpdate extends Controller {

    function insert_update(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $import = new AssaysImport($db_resource, $final_global_template_vars["session_key"]);
        $assay = new Assay($db_resource);

        $data = array();
        $current_values = array();
        $laboratory_name = "";
        $data["session"] = $_SESSION[$final_global_template_vars["session_key"]];

        if ($request->isPost()) {
            $current_values = $request->getParams();

            $data['sop_files'] = $import->get_sop_files($current_values['import_log_id']);
            $data['uploaded_files'] = isset($current_values['uploaded_files']) ? $import->get_sop_files_by_id($current_values['uploaded_files']) : false;

            // $data['uploaded_sop_file_types'] = isset($current_values['sop_file_types']) ? $current_values['sop_file_types'] : false;
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
        } elseif ($args['import_log_id']) {
            $import_log_id = $args['import_log_id'];
            $current_values = $import->get_assay_import_record($import_log_id);
            $data['sop_files'] = $import->get_sop_files($import_log_id);
            $data['publications'] = $import->get_publications($import_log_id);

            // Get the user's roles.
            $user_role_ids = isset($data["session"]["user_role_list"])
                ? $data["session"]["user_role_list"] : array();
            // Get the laboratory metadata via the import_log_id GET variable.
            $data["laboratory_data"] = $assay->get_laboratory_by_import_log_id($import_log_id);
            // Get the laboratory name for the page title (superadmin only).
            $laboratory_name = in_array(4, $user_role_ids) ? ": " . $data["laboratory_data"]["laboratory_name"] : "";
        }

        // Get all SOP File types.
        $data['sop_file_types'] = $import->get_sop_file_types();

        // Throw a 404 if no values are returned. (This means either user is not in a privileged group or an incorrect id was supplied.)
        //if (!$request->getParam('import_log_id') && !$args['import_log_id']) throw new \Slim\Exception\NotFoundException($request, $response);

        // Get assay types
        $data["assay_types"] = $import->get_assay_types();

        // Get peptide_standard_purity_types
        $data['peptide_standard_purity_options'] = $import->get_peptide_standard_purity_types();

        $data["template_versions"] = [
            [
                "template_version_id"   => 1,
                "label"                 => "Version 1"
            ],[
                "template_version_id"   => 2,
                "label"                 => "Version 2"
            ]
        ];

        $data["experiment_345"] = [
            [
                "experiment_option_id"  => 0,
                "label"                 => "No"
            ],[
                "experiment_option_id"  => 1,
                "label"                 => "Yes"
            ]
        ];


        $data = array_merge($current_values, $data);

        $this->logger->info(var_export($data, true));

        // Render
        $view = $this->container->get('view');
        $view->render($response,
            "insert_update.twig"
            , array(
                "page_title" => "Add Import Metadata" . $laboratory_name
            , "hide_side_nav" => true
            , "data" => $data
            , "errors" => isset($this->container["swpg_validation_errors"]) ? $this->container["swpg_validation_errors"] : false
            )
        );
    }

}