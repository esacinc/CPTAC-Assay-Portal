<?php

use Slim\Route;
use Slim\Slim;

function check_authenticated(Route $route){
	$app = Slim::getInstance();
	global $final_global_template_vars;
	if(!isset($_SESSION[$final_global_template_vars["session_key"]])){
		//set cookie so user can come back to this page
		setcookie($final_global_template_vars["redirect_cookie_key"], $_SERVER["REQUEST_URI"], time()+3600, "/", false,
            $final_global_template_vars["request_https"], true);
		$app->redirect($final_global_template_vars["login_url"]);
	}
}

function force_https(){
	global $final_global_template_vars;
	
	if(!$final_global_template_vars["is_dev"]){
	    $app = Slim::getInstance();
	    
		//means we are on a production box
		if(!$final_global_template_vars["request_https"]) {
			$app->redirect(("https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
		}
	}
}

/*
 * needed to create this function because the "force_https" function only forces https if the server is not marked as "dev"
 */
function force_ssl(){
    global $final_global_template_vars;
    
	if(!$final_global_template_vars["request_https"]) {
		Slim::getInstance()->redirect(("https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
	}
}

/*
 * needed to create this function because the "force_https" function only forces https if the server is not marked as "dev"
 */
function set_current_module(\Slim\App $app, $module_name, $path_to_this_module=false){

	global $swpg_global_settings;
	global $final_global_template_vars;

    $container = $app->getContainer();

	$data = array(
		"current_module_name" => false
		,"current_module_location" => false
	);

	$data["current_module_name"] = $module_name;
	if(empty($data["current_module_name"])){
		//find the default 'site' module used for housing resources for the entire site, as well as root level requests
		$data["current_module_name"] = basename($final_global_template_vars["default_site_module"]);
		$data["current_module_location"] = $final_global_template_vars["default_site_module"];
	}else{
		$data["current_module_location"] = $_SERVER["DOCUMENT_ROOT"]. $path_to_this_module;
	}

	$final_global_template_vars["active_module"] = $data["current_module_name"];
	$final_global_template_vars["path_to_this_module"] = $path_to_this_module;
	$final_global_template_vars["absolute_path_to_this_module"] = $data["current_module_location"];
	
	if(!empty($final_global_template_vars["default_site_module"]) && $data["current_module_location"] != $final_global_template_vars["default_site_module"]){
		$paths = \swpg\models\utility::get_file_paths($final_global_template_vars["default_site_module"] . '/controllers',true);
		foreach($paths as $single_path){
			include_once $single_path;
		}
	}

	require $data["current_module_location"] . "/config/settings.php";

	require $data["current_module_location"] . "/config/includes.php";




	//feel free to unset keys that are in the swpg_global_settings/swpg_module_includes js and css includes if you don't want them in this module
    if ($swpg_module_includes["js"]) {
        $final_global_template_vars["js_includes"] = array_merge($final_global_template_vars["js_includes"], $swpg_module_includes["js"]);
    } else {
        $final_global_template_vars["js_includes"] = $final_global_template_vars["js_includes"];
    }
    if($swpg_module_includes["css"]) {
        $final_global_template_vars["css_includes"] = array_merge($final_global_template_vars["css_includes"], $swpg_module_includes["css"]);
    } else {
        $final_global_template_vars["css_includes"] = $final_global_template_vars["css_includes"];
    }
	unset($swpg_module_includes);

	//USE THIS VARIABLE IN YOUR ROUTES!! Do not use swpg_module_settings
	$final_global_template_vars = array_merge($final_global_template_vars, $swpg_module_settings);
	unset($swpg_module_settings);

    $settings = $container->get('settings');
    $settings->replace([
        'templates' => $data["current_module_location"] . "/templates"
        ,'cache'    => $data["current_module_location"] . "/cache"
        , 'current_module_name' => $data['current_module_name']
    ]);

	$twig = $container->get('view')->getEnvironment();

	$template_location_directories = array(
		$data["current_module_location"] . "/templates"
		,isset($final_global_template_vars["site_templates"]) ? $final_global_template_vars["site_templates"] : null
		,$final_global_template_vars["core_templates"]
        ,$final_global_template_vars["mail_templates"]
		,$_SERVER["DOCUMENT_ROOT"]);
	$loader = new Twig_Loader_Filesystem(array_filter($template_location_directories));
	$twig->setLoader($loader); 

	foreach($final_global_template_vars as $var_name => $var_value){
		$twig->addGlobal($var_name, $var_value);
	}

	$twig->addGlobal("is_authenticated", (isset($final_global_template_vars["session_key"]) && isset($_SESSION[$final_global_template_vars["session_key"]])) ? true : false);
	$twig->addGlobal("session",$_SESSION);
	$twig->addGlobal("request_uri",$_SERVER["REQUEST_URI"]);

    // Define routes
	require $data["current_module_location"] . "/config/routes.php";

    return $data;
}

/*
 * only allow script to be run by a given IP address
 */
$force_request_address = function ($ip_address=array()){
	return function () use ($ip_address){
		$app = Slim::getInstance();
		if(empty($_SERVER["REMOTE_ADDR"]) || !in_array($_SERVER["REMOTE_ADDR"],$ip_address)){
			$app->halt(403, 'Unauthorized');
		}
	};	
};