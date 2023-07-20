<?php

namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use core\controllers\Controller;

class ExportUniProtCsv extends Controller {

    function export_uniprot(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;
        require_once $final_global_template_vars["absolute_path_to_this_module"] . "/config/settings.php";

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new Assay($db_resource, $final_global_template_vars["session_key"]);

        //get panel data from database
        $assay_data = $assay->export_uniprot();
        $fields = $final_global_template_vars['datatables'][0]['uniprot_fields'];

        //create filename for download
        $date_info = getdate();
        $filename = "CPTAC_Assay_Portal_UniProt_" . date("Y-m-d");
        $filename = str_replace(' ', '_', $filename);

        $filepath = $final_global_template_vars["temp_directory_path_via_http"] . $filename;

        $output = fopen($filepath, "w");
        fputcsv($output, $fields);
        foreach ($assay_data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);

        if (file_exists($filepath)) {
            $fh = @fopen($filepath, 'r+');

            $stream = new \Slim\Http\Stream($fh);

            return $response
                ->withHeader('Content-Type', 'text/csv')

                ->withHeader('Content-Disposition', 'attachment; filename=' . $filename . '.csv')

                ->withHeader('Content-Length', filesize($filepath))
                ->withBody($stream);
        }
        return $response;

    }

}
