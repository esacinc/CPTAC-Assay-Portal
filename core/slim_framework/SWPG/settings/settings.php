<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/swpg_global_settings.php";

$swpg_core_settings = array(

	"core_framework_db" => array(
		"type" => "mysql"
		,"name" => $core_db_conn['name']
		,"host" => $core_db_conn['host']
		,"user" => $core_db_conn['user']
		,"password" => $core_db_conn['password']
		,"die_on_connection_failure" => false
		,"connection_error_message" => "The system is currently not accessible."
		,"email_on_connection_failure" => true
		,"admin_emails" => "AssayPortal.admin@esacinc.com"
	)
	,"path_to_swpg_files" => "/swpg_files/"
	,"default_span_number" => 9//the twitter bootstrap span number (width) you want the main content to have by default
	,"js_includes" => array() //this is just a place holder, I doubt that we would want to include something automatically across all of our websites
	,"css_includes" => array() //this is just a place holder, I doubt that we would want to include something automatically across all of our websites
	,"default_site_module" => $_SERVER["DOCUMENT_ROOT"]. "/site" //this directory needs to be present, but can be changed at the site level
	,"resource_prepend" => ""
	,"navbar" => "" // provide the path to a local navbar and include it to override the default navbar
);
?>