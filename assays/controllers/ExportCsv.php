<?php

namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use core\controllers\Controller;

class ExportCsv extends Controller {

    function export_csv(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;
        require_once $final_global_template_vars["absolute_path_to_this_module"] . "/config/settings.php";

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new Assay($db_resource, $final_global_template_vars["session_key"]);
        $data = false;

        $side_bar_filter = array();
        $dropdown_filter = array();

        $sortable_key_fields = array_keys($final_global_template_vars['datatables'][0]['fields']);
        $posted_data = json_decode($request->getParam('csv_filter'), true);

        //$this->container->get('logger')->info($posted_data);

        $side_bar_filter = $posted_data['sidebar_filter'];
        $dropdown_filter = $posted_data['dropdown_filter'];
        $search_filter = $posted_data['search_string'];

        $assay_data = $assay->export_csv($side_bar_filter, $dropdown_filter, $search_filter);
        $fields = $final_global_template_vars['datatables'][0]['fields'];

          //$this->container->get('logger')->info($assay_data['sql']);
        $labels = array();
        foreach ($fields as $field) {
            array_push($labels, $field["label"]);
        }
        $date_info = getdate();
        $filename = "CPTAC_Assays_export_" . $date_info['year'] . "-" . $date_info['mon'] . "-" . $date_info['mday'] . "-" . $date_info['hours'] . "-" . $date_info['minutes'];
        $filepath = $final_global_template_vars["temp_directory_path_via_http"] . $filename;

        $output = fopen($filepath, "w");
        fputcsv($output, $labels);
        foreach ($assay_data['data'] as $row) {
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
