<?php
$swpg_module_settings = array(
	"module_name" => "Authenticate"
	,"module_description" => "Authenticate users"
	,"menu_hidden" => true
    ,"navbar" => "/site/templates/admin_navbar_update.php"
    ,"layout_template_name" => "swpg_bootstrap_admin_non_responsive.twig"
	,"pages" => array(
		array(
			"label" => "Login", "path" => "/", "display" => true
		)
	)
    ,"password_reset_expiration_interval" => (60 * 60 * 24 * 2)
	,"session_keys" => array(
		"username"
		,"sn"
		,"country_abbr"
		,"location"
		,"state_abbr"
		,"email"
		,"given_name"
		,"ned_id"
	)
	,"wsdl" => 'https://soa.nih.gov/NEDPerson/NEDPersonOPSv2/WSDLNEDPersonOP-service.serviceagent?wsdl' //SOAP client for NED web service
	,"endpoint" => 'https://soa.nih.gov/NEDPerson/NEDPersonOPSv2/WSDLNEDPersonOP-service.serviceagent/PortTypeEndpoint0' //endpoint for SOAP NED web service call
	,"wss_ns" => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd' //namespace for SOAP NED web service call
    ,"remove_side_nav" => true
);
?>
