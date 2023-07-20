<?php
global $user_account_permissions;
global $user_account_delete_permissions;
$user_account_permissions = function(\Slim\Route $route){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	$params = $route->getParams();
	
	$record_account_id = isset($params["account_id"]) ? $params["account_id"] : false;
	$session_account_id = !empty($_SESSION[$final_global_template_vars["session_key"]]) && !empty($_SESSION[$final_global_template_vars["session_key"]]["account_id"]) ? $_SESSION[$final_global_template_vars["session_key"]]["account_id"] : false;
	
	if(empty($session_account_id) || empty($record_account_id)){
		$app->redirect($final_global_template_vars["access_denied_url"]);
	}
	
	//check to see if the user is trying to modify their own record
	if($session_account_id == $record_account_id){
		$has_permission = array_intersect($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"], $final_global_template_vars["role_permissions"]["user_account"]["modify_own_account"]);
		if(empty($has_permission)){
			$app->flash('message', 'You are not able to modify your own user account.');
			$app->redirect($final_global_template_vars["access_denied_url"]);
		}
	}
};

$user_account_delete_permissions = function(\Slim\Route $route){
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	$params = $route->getParams();
	
	$has_permission = array_intersect($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"], $final_global_template_vars["role_permissions"]["user_account"]["delete"]);
	if(empty($has_permission)){
		$app->redirect($final_global_template_vars["access_denied_url"]);
	}
};