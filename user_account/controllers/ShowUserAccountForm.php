<?php

namespace users\controllers;
use Slim\Http\Request;
use Slim\Http\Response;

use users\models\UserAccountDao;

use core\controllers\Controller;

class ShowUserAccountForm extends Controller {

function show_user_account_form(Request $request, Response $response, $args = []){
	//$app = \Slim\Slim::getInstance();
	//$env = $app->environment();
	global $final_global_template_vars;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$useraccount = new \user_account\models\UserAccountDao($db_resource,$final_global_template_vars["session_key"]);
	$group = new \group\models\GroupDao($db_resource,$final_global_template_vars["session_key"]);
	//$post = $app->request()->post();
	$account_id = $args['account_id'];

	$current_values = false;
	if($request->isPost()){

	}elseif($account_id){

	}
	$current_group_values = $useraccount->get_user_group_roles_map($account_id,$final_global_template_vars["proxy_id"]);

	$roles = $useraccount->get_roles($final_global_template_vars["exclude_ids_from_selector"]);

	$group_hierarchy = $group->get_group_hierarchy("--");
	$flat_group_hierarchy = $group->flatten_group_hierarchy($group_hierarchy);
	foreach($flat_group_hierarchy as $array_key => &$single_group_info){

		$single_group_info["admin"] = false;
		$show_all = array_intersect($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"], $final_global_template_vars["role_permissions"]["user_account"]["assign_to_any_group"]);

		if(!empty($show_all)){
			$single_group_info["admin"] = true;
		}else{
			$group_roles = $useraccount->has_role($_SESSION[$final_global_template_vars["session_key"]]["account_id"],$final_global_template_vars["administrator_id"],$single_group_info["group_id"]);
      //$this->container->get('logger')->info($group_roles);

			if(!empty($group_roles)){
				$single_group_info["admin"] = true;
			}
		}

	}


	$user_account_info = $useraccount->get_user_account_info($account_id);

	/*
	$app->render('user_account_form.php',array(
		"page_title" => "Manage User Account"
		,"roles" => $roles
		,"groups" => $flat_group_hierarchy
		,"account_info" => $user_account_info
		,"user_account_groups" => $current_group_values
		,"errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
	));
	*/
  //$this->container->get('logger')->info($roles);
  //$this->container->get('logger')->info($user_account_info);
	//$this->container->get('logger')->info($current_group_values);

	$view = $this->container->get('view');
					$view->render(
							$response
							, 'user_account_form.php'
							,array(
								"page_title" => "Manage User Account"
								,"roles" => $roles
								,"groups" => $flat_group_hierarchy
								,"account_info" => $user_account_info
								,"user_account_groups" => $current_group_values
								,"errors" => isset($env["swpg_validation_errors"]) ? $env["swpg_validation_errors"] : false
							)
					);

					return $response;


}
}
?>
