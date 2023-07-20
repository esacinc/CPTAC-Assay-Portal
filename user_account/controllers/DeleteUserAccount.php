<?php
namespace users\controllers;
use Slim\Http\Request;
use Slim\Http\Response;

use users\models\UserAccountDao;

use core\controllers\Controller;

class DeleteUserAccount extends Controller {

function delete_user_account(Request $request, Response $response, $args = []){

	//$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$useraccount = new \user_account\models\UserAccountDao($db_resource,$final_global_template_vars["session_key"]);

	$post = $request->getParsedBody();
	$delete_ids = json_decode($post["id"]);
	foreach($delete_ids as $single_id){
		$useraccount->delete_user_account($single_id);
	}
}
}

?>
