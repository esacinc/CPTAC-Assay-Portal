<?php
/**
 * @desc Import Assays: controller for browsing assay import sets
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
namespace assays_import\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

class BrowseAssayImports extends Controller {

    function browse_assay_imports(Request $request, Response $response, $args = []) {
        $view = $this->container->get('view');
        $view->render($response, 'browse_assay_imports.twig', array(
            "page_title" => "Browse Imports"
        , "uniquehash" => uniqid()
        ));
    }

}