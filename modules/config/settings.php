<?php
/* Note that you are able to use any key that exists in
 * the global settings, and it will overwrite it
 */


$swpg_module_settings = array(
	"module_name" => "Modules"
	,"module_description" => "Display all available modules."
	,"module_icon_css_classes" => "fa fa-fw fa-object-group"
	,"menu_hidden" => true
    ,"navbar" => "/site/templates/admin_navbar_update.php"
    ,"layout_template_name" => "swpg_bootstrap_admin_non_responsive.twig"
	,"pages" => array(
		array(
			"label" => "Dashboard", "path" => "/", "display" => true
		)
	)
	,"sort_order" => 21
    ,"remove_side_nav" => true
);
