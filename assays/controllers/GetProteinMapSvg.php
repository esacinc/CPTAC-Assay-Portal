<?php

namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\WikiPathway;

use core\controllers\Controller;

class GetProteinMapSvg extends Controller {

    function get_protein_map_svg(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $uniprot_ac_id = $request->getParam('uniprot_ac_id');

        if (isset($uniprot_ac_id) && !empty($uniprot_ac_id)) {
            $phosphosites_graph_file = $final_global_template_vars["phosphosite_images_storage_path"] . "/svg/" . $uniprot_ac_id . "cache.svg";

            $data = file_get_contents($phosphosites_graph_file);

            $response->write($data);
            return $response->withHeader('Content-Type', 'application/text');
        }

        return false;

    }

}
