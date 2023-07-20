<?php

namespace public_upload\controllers;

use assays_preview\models\AssayPreview;
use Slim\Http\Request;
use Slim\Http\Response;

use assays_preview\models\AssaysPreview;
use core\controllers\Controller;

class DatatablesBrowseAssaysPreview extends Controller {

    function datatables_browse_assays_manage(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new AssayPreview($db_resource, $final_global_template_vars["session_key"]);

        $side_bar_filter = array();
        $column_filter = $request->getParam('column_filter');
        $column_filters = $column_filter ? json_decode($column_filter) : false;

        $sortable_key_fields = array_keys($final_global_template_vars['datatables'][0]['fields']);

        $side_bar_filter = json_decode($request->getParam('sidebar_filter'), true);

        $data = $assay->browse_assays_public($sortable_key_fields[$request->getParam('iSortCol_0')]
            , $request->getParam('sSortDir_0')
            , $request->getParam('iDisplayStart')
            , $request->getParam('iDisplayLength')
            , $request->getParam('sSearch')
            , $column_filters
            , $final_global_template_vars['datatables'][0]['fields']
            , $side_bar_filter);

        $data['sEcho'] = (int)$request->getParam('sEcho');

        return $response->withJson($data);
    }

}
