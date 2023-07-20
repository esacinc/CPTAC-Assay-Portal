<?php
/* Note that you are able to use any key that exists in
 * the global settings, and it will overwrite it
 */
$swpg_module_settings = array(
	"public_navbar" => "/site/templates/public_navbar_update.php"
    ,"layout_template_name" => "swpg_bootstrap_public_upload.twig"
    ,"preview_layout_template_name" => "swpg_bootstrap_admin_non_responsive_v1.twig"
	,"module_name" => "Support"
	,"module_description" => "Website support"
	,"module_icon_css_classes" => "fa fa-fw fa-bell"
	,"menu_hidden" => isset($_SESSION[$swpg_global_settings["session_key"]]) && $_SESSION[$swpg_global_settings["session_key"]] ? false : true
    ,"navbar" => "/site/templates/admin_navbar_update.php"
	,"site_key" => "cptac"

	,"sort_order" => 19
	,"db_connection" => $final_global_template_vars["core_framework_db"]
	,"browse_fields" => array(
		"manage" => array("handle" => "manage", "label" => "","filter" => false)
		,"submitter" => array("handle" => "submitter", "label" => "Submitter", "comparison_default" => "start_with", "filter" => true)
		,"title" => array("handle" => "title", "label" => "Title", "comparison_default" => "start_with", "filter" => true)
		,"body" => array("handle" => "body", "label" => "Body", "comparison_default" => "start_with", "filter" => true)
		,"created_date" => array("handle" => "created_date", "label" => "Created Date", "comparison_default" => "start_with", "filter" => true)
		,"category" => array("handle" => "category", "label" => "Category", "comparison_default" => "start_with", "filter" => true)
	)
	,"categories_browse_fields" => array(
		"manage" => array("handle" => "manage", "label" => "","filter" => false)
		,"name" => array("handle" => "name", "label" => "Name", "comparison_default" => "start_with", "filter" => true)
	)
	,"file_validation" => array(
		"max_size" => 1000000
		,"allowed_mime_types" => array(
			"application/msword"
			,"application/vnd.openxmlformats-officedocument.wordprocessingml.document"
			,"image/jpeg"
			,"image/pjpeg" //freakin IE
			,"application/pdf"
			,"image/png"
			,"image/x-png" //freakin IE
			,"text/plain"
			,"application/vnd.ms-excel"
			,"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
			,"application/rtf"
			,"image/gif"
		)
	)
    , "remove_side_nav" => true
);
?>
