<?php
/* Note that you are able to use any key that exists in
 * the global settings, and it will overwrite it
 */
$swpg_module_settings = array(
	"module_name" => "User Accounts"
	,"module_description" => "Manage user accounts for application"
	,"module_icon_css_classes" => "fa fa-fw fa-user-circle"
	,"menu_hidden" => isset($_SESSION[$swpg_global_settings["session_key"]]) && $_SESSION[$swpg_global_settings["session_key"]] ? false : true
	//,"navbar" => "/site/templates/admin_navbar.php"
	,"navbar" => "/site/templates/admin_navbar_v1_update.php"
	,"pages" => array(
		array(
			"label" => "Browse User Accounts", "path" => "/", "display" => $apply_permissions("user_account", "browse_access")
		)
		,array(
			"label" => "Find User Account", "path" => "/find", "display" => $apply_permissions("user_account", "manage_access")
		)
	)
	,"sort_order" => 5
	,"browse_fields" => array(
		"manage" => array("handle" => "manage", "label" => "","filter" => false)
		,"name" => array("handle" => "name", "label" => "Name", "comparison_default" => "start_with", "filter" => true)
		,"groups" => array("handle" => "groups", "label" => "Groups", "comparison_default" => "start_with", "filter" => true)
	)
	  ,"layout_template_name" => "swpg_bootstrap_admin_non_responsive_v1.twig"
	//	,"layout_template_name" =>"swpg_bootstrap_admin_non_responsive.twig"
	,"proxy_id" => false //this enables user account to add people to proxy role
	,"administrator_id" => array(3) //used to allow a user to add roles to the group they are an admin for
	,"exclude_ids_from_selector" => array() //don't allow user to assign these roles
    , "remove_side_nav" => true
);
?>
