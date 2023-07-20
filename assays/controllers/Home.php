<?php
namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use assays\models\Kegg;
use core\controllers\Controller;

class Home extends Controller {
    function home(Request $request, Response $response, $args = [])
    {
        global $final_global_template_vars;

        //$response->withRedirect("https://proteomics.cancer.gov/assay-portal", 301);

        $view = $this->container->get('view');

        $view->render($response, 'home.twig', array(
            "page_title" => "Test"
        ,"hide_side_nav" => true
        ));
        return $response;

    }

}
