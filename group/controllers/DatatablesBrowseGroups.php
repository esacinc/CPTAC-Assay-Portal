<?php

namespace group\controllers;
use Slim\Http\Request;
use Slim\Http\Response;

use group\models\GroupDao;

use core\controllers\Controller;

class DatatablesBrowseGroups extends Controller {

		function datatables_browse_groups(Request $request, Response $response, $args = []){
				global $final_global_template_vars;
				$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
				$db_resource = $db_conn->get_resource();
				$group = new \group\models\GroupDao($db_resource,$final_global_template_vars["session_key"]);

				$column_filter = $request->getParam('column_filter');
				$column_filters = $column_filter ? json_decode($column_filter) : false;

				$sortable_key_fields = array_keys($final_global_template_vars["browse_fields"]);

				$data = $group->browse_groups(
								$sortable_key_fields[$request->getParam('iSortCol_0')]
								, $request->getParam('sSortDir_0')
								, $request->getParam('iDisplayStart')
								, $request->getParam('iDisplayLength')
								, $request->getParam('sSearch')
								, $column_filters
								, $final_global_template_vars["browse_fields"]
						);

				return $response->withJson($data);

			  }

}
