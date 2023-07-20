<?php
/* Note that you are able to use any key that exists in
 * the global settings, and it will overwrite it
 */
$swpg_module_settings = array(
	"module_name" => "Groups"
	,"module_description" => "Manage groups for application"
	,"module_icon_css_classes" => "fa fa-fw fa-users"
	,"menu_hidden" => isset($_SESSION[$swpg_global_settings["session_key"]]) && $_SESSION[$swpg_global_settings["session_key"]] ? false : true
	,"navbar" => "/site/templates/admin_navbar_v1_update.php"


	,"sort_order" => 6
	,"browse_fields" => array(
		"manage" => array("handle" => "manage", "label" => "","filter" => false)
		,"name" => array("handle" => "name", "label" => "Name", "comparison_default" => "start_with", "filter" => true)
		,"abbreviation" => array("handle" => "abbreviation", "label" => "Abbreviation", "comparison_default" => "start_with", "filter" => true)
		,"description" => array("handle" => "description", "label" => "Description", "comparison_default" => "start_with", "filter" => true)
		,"address" => array("handle" => "address", "label" => "Address", "comparison_default" => "start_with", "filter" => true)
		,"city" => array("handle" => "city", "label" => "City", "comparison_default" => "start_with", "filter" => true)
		,"last_modified" => array("handle" => "last_modified", "label" => "Last Modified", "comparison_default" => "start_with", "filter" => true)
	)
    , "remove_side_nav" => true
		,"layout_template_name" => "swpg_bootstrap_admin_non_responsive_v1.twig"
);
?>
