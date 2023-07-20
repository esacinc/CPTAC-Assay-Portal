<?php

namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use core\controllers\Controller;

class ExportMultiplex extends Controller {

    function export_multiplex(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;
        require_once $final_global_template_vars["absolute_path_to_this_module"] . "/config/settings.php";

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new Assay($db_resource, $final_global_template_vars["session_key"]);

        $lab_name = $request->getParam('multiplex_csv_name_filter');
        $panel_name = $request->getParam('multiplex_csv_description_filter');

        //get panel data from database
        $assay_data = $assay->export_multiplex($lab_name, $panel_name);
        $fields = $final_global_template_vars['datatables'][0]['fields'];

        //get field labels for column headers
        $labels = array();
        foreach ($fields as $field) {
            array_push($labels, $field["label"]);
        }

        //create the report header for top of csv
        $report_title = array();
        array_push($report_title, $lab_name . "-" . $panel_name);

        //create filename for download
        $date_info = getdate();
        $filename = "CPTAC_Assay_Panel_{$lab_name}_" . date("Y-m-d");
        $filename = str_replace(' ', '_', $filename);

        $filepath = $final_global_template_vars["temp_directory_path_via_http"] . $filename;


        $output = fopen($filepath, "w");
        fputcsv($output, $report_title);
        fputcsv($output, $labels);
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
