<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use public_upload\models\PublicUpload;

use core\controllers\Controller;

use \GUMP;


class InsertUpdateAssaysImport extends Controller {

    function insert_update_assays_import(Request $request, Response $response, $args = []) {


        global $final_global_template_vars;
        $session_key = $final_global_template_vars["session_key"];



        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $import = new PublicUpload($db_resource, $session_key);

        $gump = new GUMP();


        $post = $request->getParsedBody();

        if ($_SESSION[$session_key]['import_log_id']) {
            $import_log_id = $_SESSION[$session_key]['import_log_id'];
        } else {
            $import_log_id = !empty($post["import_log_id"]) ? $post["import_log_id"] : false;
        }

        //$this->logger->info("import log id " . $import_log_id);

        // Default rules
        $rules = array(
              "instrument" => "required"
            , "matrix" => "required"
            , "matrix_amount_and_units" => "required"
            , "quantification_units" => "required"
            , "internal_standard" => "required"
                //,"peptide_standard_purity" => "required"
            , "peptide_standard_purity_types_id" => "required"
            , "protein_species_label" => "required"
            , "data_type" => "required"
            , "lc" => "required"
            , "column_packing" => "required"
            , "column_dimensions" => "required"
            , "column_temperature" => "required"
            , "flow_rate" => "required"
            , "mobile_phase_a" => "required"
            , "mobile_phase_b" => "required"
            //, "celllysate_path" => "required"
        );

        // Assay types rules
        $rules_assay_types = array();

        if (isset($post["assay_types_id"])) {
            switch ($post["assay_types_id"]) {
                case "1":
                    $rules_assay_types = array(
                        "enrichment_method" => "required"
                        // ,"affinity_reagent_type" => "required"
                    , "antibody_vendor" => "required"
                    , "media" => "required"
                        // ,"antibody_portal_url" => "required"
                    );
                    break;
                case "2":
                    $rules_assay_types = array(
                        "fractionation_approach" => "required"
                    , "column_material" => "required"
                    , "conditions" => "required"
                    , "number_of_fractions_collected" => "required"
                    , "number_of_fractions_analyzed" => "required"
                    , "fraction_combination_strategy" => "required"
                    );
                    break;
            }
        }

        // Merge the default rules and the assay types rules
        $rules = array_merge($rules, $rules_assay_types);
        $validated = $gump->validate($post, $rules);
        $errors = array();
        if ($validated !== TRUE) {
            $errors = \swpg\models\utility::gump_parse_errors($validated);
        }

        //$this->logger->info(var_export($errors, true));

        if (!$errors) {

            //insert updates for import
            $data = $import->insert_update_assays_import($post, $import_log_id,$_SESSION[$session_key]['account_id'] );

            $import_log_id = $data["import_log_id"];

            //set session import_log_id
            $_SESSION[$session_key]["import_log_id"] = $import_log_id;

            $message = $import_log_id
                ? 'updated.'
                : 'entered into the database.';
            return $response->withRedirect($final_global_template_vars["path_to_this_module"] . '/upload_skyline_file');
        } else {
            $this->container["swpg_validation_errors"] = $errors;
        }
    }

}
