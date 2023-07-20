<?php

namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use assays\models\LabkeyApi;
use core\controllers\Controller;

class GetPlotsTableData extends Controller {

    function get_plots_table_data(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;
        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $assay = new Assay(
            $db_resource
            , $final_global_template_vars["session_key"]
        );

        $genes = false;
        $post = (string)$request->getParam("genes");
        $gene_data = json_decode($post, true);

        $i = 0;

        foreach ($gene_data as $gene_value) {

            $genes[$i]['lod_loq_comparison_data'] = false;
            $genes[$i]['response_curves_data'] = false;

            // LOD and LLOQ data
            $genes[$i]['lod_loq_comparison_data'][] = $assay->get_lod_loq_comparison_data(
                $gene_value['peptide_modified_sequence']
                , $gene_value['laboratory_id']
                , $gene_value['manage_id']
            );

            // Response Curves data
            $genes[$i]['response_curves_data'][] = $assay->get_response_curves_data(
                $gene_value['peptide_modified_sequence']
                , $gene_value['laboratory_id']
                , $gene_value['manage_id']
            );

            $genes[$i]['laboratory_abbreviation'] = $gene_value['laboratory_abbreviation'];
            $genes[$i]['peptide_sequence'] = $gene_value['peptide_modified_sequence'];
            $genes[$i]['peptide_standard_purity_types_id'] = $gene_value['peptide_standard_purity_types_id'];
            $genes[$i]['manage_id'] = $gene_value['manage_id'];

            $i++;
        }

        return $response->withJson($genes);
    }

}