<?php
/**
 * @desc Import Assays: controller for browsing assay import sets for datatables
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

use assays_import\models\AssaysImport;

class DatatablesBrowseAssayImports extends Controller {

    function datatables_browse_assay_imports(Request $request, Response $response, $args = []) {
      global $final_global_template_vars;

      $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);

      $db_resource = $db_conn->get_resource();

      $import = new AssaysImport($db_resource, $final_global_template_vars["session_key"]);


      $column_filter = $request->getParam('column_filter');
      $column_filters = $column_filter ? json_decode($column_filter) : false;

      $sortable_key_fields = array_keys($final_global_template_vars["browse_fields"]);

      $data = $import->browse_assay_imports(
         $request->getParams()
      );

      return $response->withJson($data);
    }

}
