<?php
namespace assays_preview\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays_preview\models\AssayPreview;

use core\controllers\Controller;

class GenerateCsv extends Controller {

    function generate_csv(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;
        $logger = $this->container->get('logger');

        $logger->info("generate csv");

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssayPreview($db_resource, $final_global_template_vars["session_key"]);

        $post = $request->getParams();
        $csv_link = 'test';

        $import_log_id = (int)$post['import_log_id'];


        $logger->info("import set id " . $import_log_id);

        // Create the CSV file
        if ($import_log_id) {

            // Get the array for the CSV
            $notes = $assay->get_notes_by_import_set_id($import_log_id);

            if ($notes) {
                $path_to_temp_directory = $_SERVER['DOCUMENT_ROOT'] . '/swpg_files/cptac/temp/';
                $path_to_temp_directory_via_http = $_SERVER['DOCUMENT_ROOT'] . '/swpg_files/cptac/temp/';
                $filename = "CPTAC_" . date("YmdHis") . "_all_notes.csv";
                $fp = fopen($path_to_temp_directory . $filename, 'w');
                fputcsv($fp, array('cptac_id', 'gene_symbol', 'peptide_sequence', 'note_content', 'note_submitted_by', 'created_date'));
                foreach ($notes as $note) {
                    fputcsv($fp, $note);
                }
                fclose($fp);
            }

            $csv_link = 'https://' . $_SERVER["SERVER_NAME"] . '/swpg_files/cptac/temp/' . $filename;

        }

        $logger->info($csv_link);

        return $response->withJson($csv_link);
    }

}