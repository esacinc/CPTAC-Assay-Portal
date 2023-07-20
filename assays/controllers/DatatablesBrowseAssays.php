<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 11/20/17
 * Time: 1:02 PM
 */

namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;
use assays\models\Assay;

class DatatablesBrowseAssays extends Controller {

  function datatables_browse_assays(Request $request, Response $response, $args = []) {

      global $final_global_template_vars;
      require_once $final_global_template_vars["absolute_path_to_this_module"] . "/config/settings.php";
      $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
      $db_resource = $db_conn->get_resource();
      $assay = new Assay($db_resource,$final_global_template_vars["session_key"]);
      $data = false;

      $side_bar_filter = array();

      $sortable_key_fields = array_keys($final_global_template_vars['datatables'][0]['fields']);

      $side_bar_filter = json_decode($request->getParam('sidebar_filter'), true);

      $species_filter = $request->getParam("species_filter");
      $assay_type_filter = $request->getParam("assay_type_filter");
      $antibody_filter = $request->getParam("antibody_filter");


      //$data = $assay->browse_assays($request->getParams());
      $data = $assay->browse_assays_updated($request->getParams());



      return $response->withJson($data);
  }

}
