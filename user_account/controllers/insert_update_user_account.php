<?php
function insert_update_user_account(\Slim\Route $route){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	//require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/GUMP/gump.class.php";
	//url parameters matched in the route
	$params = $route->getParams();
	$account_id = isset($params["account_id"]) ? $params["account_id"] : false;
	$db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
	$db_resource = $db_conn->get_resource();
	$useraccount = new \user_account\models\UserAccountDao($db_resource,$final_global_template_vars["session_key"]);
	$group = new \group\models\GroupDao($db_resource,$final_global_template_vars["session_key"]);
	$post = $app->request()->post();
	$errors = false;
	//will probably need the following commented code if we add anything other than groups
	/*
	$gump = new GUMP();
	$rules = array(
		"name" => "required"
	);
	$validated = $gump->validate($app->request()->post(), $rules);
	$errors = array();
	if($validated !== TRUE){
		$errors = \swpg\models\utility::gump_parse_errors($validated);
	}
	*/
	if(!empty($post)){
		$current_group_values = $useraccount->get_user_group_roles_map($account_id,$final_global_template_vars["proxy_id"]);
		$proposed_group_value = json_decode($post["group_data"],true);
		$changes = array();
		$current_group_role_array = array();
		$proposed_group_role_array = array();
		foreach($proposed_group_value as $single_group_info){
			foreach($single_group_info["roles"] as $single_role_id){
				$tmp_array = array(
					"group_id" => $single_group_info["group_id"]
					,"role_id" => $single_role_id
				);
				$proposed_group_role_array[] = json_encode($tmp_array);
			}
		}
		
		foreach($current_group_values as $single_group_info){
			foreach($single_group_info["roles"] as $single_role_id){
				$tmp_array = array(
					"group_id" => $single_group_info["group_id"]
					,"role_id" => $single_role_id
				);
				$current_group_role_array[] = json_encode($tmp_array);
			}
		}
		$changes = array_diff($proposed_group_role_array,$current_group_role_array);
		$changes = array_merge($changes, array_diff($current_group_role_array, $proposed_group_role_array));

		/**
		 * check to see if the user is trying to hack the system and add a role they are not able to 
		 **/
		 foreach($changes as $single_change){
		 	$single_change_array = json_decode($single_change,true); 
			$show_all = array_intersect($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"], $final_global_template_vars["role_permissions"]["user_account"]["assign_to_any_group"]);
			if(!empty($show_all)){
				//this user can add any group to any user
			}else{
				$group_roles = $useraccount->has_role($_SESSION[$final_global_template_vars["session_key"]]["account_id"],$final_global_template_vars["administrator_id"],$single_change_array["group_id"]);
				if(empty($group_roles)){
					$failed_group = $group->get_group_record($single_change_array["group_id"]);
					$errors[] = "You are not able to administrator group: " . $failed_group["name"];
				}
			}
		 	
		 }
		
		//check to see if the user is trying to add a role to a group they are not able to
		foreach($changes as $single_change){
			$single_change_array = json_decode($single_change,true); 
			if(in_array($single_change_array["role_id"],$final_global_template_vars["exclude_ids_from_selector"])){
				$errors[] = "You are not able to administrator that role.";
			}
		}
	}
	
	
	
	if(!$errors){
		
		$useraccount->insert_update_user_account($post,$account_id,true,$final_global_template_vars["proxy_id"]);
		$app->redirect($final_global_template_vars["path_to_this_module"]);
	}else{
		$env = $app->environment();
		$env["swpg_validation_errors"] = $errors;
	}
}
?>