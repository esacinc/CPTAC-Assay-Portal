<?php
/**
 * @desc Tutorials: controller for tutorials
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

 namespace tutorials\controllers;

 use Slim\Http\Request;
 use Slim\Http\Response;

 use core\controllers\Controller;

class Tutorials extends Controller {
function tutorials(Request $request, Response $response, $args = []) {

  global $final_global_template_vars;

  $view = $this->container->get('view');
  $view->render($response, 'tutorials.php', array(
    "page_title" => "Importing Assays: Quick Start Guide"
    ,"hide_side_nav" => true
    ,"final_global_template_vars" => $final_global_template_vars
  ));


}
}
?>
