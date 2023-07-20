<?php

namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\WikiPathway;

use core\controllers\Controller;

class GetWikiSvg extends Controller {

    function get_wiki_svg(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $wikipathway = new WikiPathway($db_resource);

        $wikipathway_id = $request->getParam('wikipathway_id');

        if (isset($wikipathway_id) && !empty($wikipathway_id)) {
            $wikipathway_id = (int)$wikipathway_id;

            $pathway = $wikipathway->find_wikipathway_by_id($wikipathway_id);

            $file_name = $_SERVER['PATH_TO_DATA'] . '/assay_portal/wikipathway/svg/' . $pathway['filename'];

            $data = [];

            $data['svg'] = file_get_contents($file_name);
            $data['wp_id'] = $pathway['wp_id'];

            //$response->write($data);
            //return $response->withHeader('Content-Type', 'application/text');

            return $response->withJson($data);
        }

        return false;

    }

}
