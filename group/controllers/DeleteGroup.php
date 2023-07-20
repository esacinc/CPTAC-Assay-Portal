<?php

namespace group\controllers;
use Slim\Http\Request;
use Slim\Http\Response;

use group\models\GroupDao;

use \GUMP;

use core\controllers\Controller;


class DeleteGroup extends Controller {


function delete_group(Request $request, Response $response, $args = []){
	//$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$group = new \group\models\GroupDao($db_resource,$final_global_template_vars["session_key"]);
	$post = $request->getParsedBody();
	$delete_ids = json_decode($post["id"]);
	foreach($delete_ids as $single_id){
		$group->delete_group($single_id);
	}
}

}
?>
