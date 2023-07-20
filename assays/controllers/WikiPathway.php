<?php
namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use assays\models\Kegg;
use core\controllers\Controller;

class WikiPathway extends Controller {
    function home(Request $request, Response $response, $args = [])
    {
        global $final_global_template_vars;

        $view = $this->container->get('view');

        $view->render($response, 'wikipathway.twig', array(
            "page_title" => "Wiki Pathway"
        ,"hide_side_nav" => true
        ));
        return $response;

    }

}