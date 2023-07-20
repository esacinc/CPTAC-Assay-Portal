<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/site/library/functions/functions.php');

/*
 * This function is used ONLY to make sure if a user has a sufficient role to be on a page.  NOT to apply permissions as to what the user can view ON that page
 */
global $apply_permissions;
$apply_permissions = function($permission_section, $permission_level) {
	return function ($redirect = true) use ($permission_section,$permission_level) {
		global $final_global_template_vars;
		$user_roles =  !empty($_SESSION[$final_global_template_vars["session_key"]])
                    && !empty($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"])
                    ?  $_SESSION[$final_global_template_vars["session_key"]]["user_role_list"] : array();

		$has_permission = array_intersect($user_roles, $final_global_template_vars["role_permissions"][$permission_section][$permission_level]);

		if (empty($redirect)) {
			if(empty($has_permission)) {
				return false;
			} else {
				return true;
			}
		}
		/*
		else {
			if(empty($has_permission)){
                return $container['response']->withRedirect($final_global_template_vars["access_denied_url"]);
			}
		}
		*/
	};
};
/*
function apply_permissions(\Slim\Route $route){
	//if the request is from word, don't accept...it horks the session
	if(stristr($_SERVER['HTTP_USER_AGENT'], 'Word')){
	    die();
	}
	$app = \Slim\Slim::getInstance();
	global $final_global_template_vars;
	//$_SESSION[$final_global_template_vars["session_key"]]
}*/
?>