<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use public_upload\models\PublicUpload;
use assays\models\Assay;

use core\controllers\Controller;

class ShowDashboard extends Controller {
    function show_dashboard(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $view = $this->container->get('view');
        $view->render($response,
            'show_dashboard.twig'
            , array(
                "page_title" => "Public Upload"
            ));
        return $response;
    }
}

?>
