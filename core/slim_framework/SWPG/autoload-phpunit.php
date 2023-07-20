<?php

use core\models\Logging\LoggerBuilder;
use core\models\Slim\LoggerLogWriter;
use GuzzleHttp\Psr7\Uri;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use core\controllers\ErrorHandler;

use Slim\Http\Response;
use Slim\App;
use Slim\Views\Twig;
use swpg\models\utility;

    if (session_status() === PHP_SESSION_NONE) {
		//session_start();
	}

	$final_global_template_vars = array(
		"is_dev" => false
	);
	if(!empty($_SERVER["IS_DEV"]) && ($_SERVER["IS_DEV"] == "true")){
		$final_global_template_vars["is_dev"] = true;
	}

	//get core settings - default settings that propogate across all sites
	require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/SWPG/settings/settings.php";
	$final_global_template_vars = array_merge($final_global_template_vars, $swpg_core_settings);

	//get the site settings (swpg_global_settings) if it exists
	if(file_exists($_SERVER["DOCUMENT_ROOT"] . "/swpg_global_settings.php")){
		require_once $_SERVER["DOCUMENT_ROOT"] . "/swpg_global_settings.php";
		$final_global_template_vars = array_merge($final_global_template_vars, $swpg_global_settings);
	} else {
		$swpg_global_settings = array();
	}

    require_once $_SERVER["DOCUMENT_ROOT"] . "/site/library/functions/functions.php";

    /**
     * Apache (and likely other) web servers use the values "on" or "off"; using {@see empty} is incorrect.
     */
	$final_global_template_vars["request_https"] = (!empty($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] !== "off"));

	// Prepare app
	$app = new App($final_global_template_vars['app_config']);

    $container = $app->getContainer();

    $container['logger'] = function($container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);

        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

        //$logger->pushHandler(new FirePHPHandler());
        return $logger;
    };

    $logger = $container->get('logger');

    // Service factory for the ORM
    $container['db'] = function ($container) {
        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($container['settings']['db']);
        $capsule->addConnection($container['settings']['db'], 'db');

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    };

    $connection = $container->get('db');

    // Register component on container
    $container['view'] = function ($container) {
        $view = new \Slim\Views\Twig($_SERVER["PATH_TO_CORE"] . '/templates'
            //, [
            //'cache' => $_SERVER["DOCUMENT_ROOT"] . '/core/cache'
            //]
        );

        // Add extensions
        $view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $container->get('request')->getUri()));
        $view->addExtension(new Twig_Extension_Debug());

        return $view;
    };

    //$container['guzzle_client'] =  new \GuzzleHttp\Client($final_global_template_vars['guzzle_config']);

    $container['errorHandler'] = function ($container) {
        //return new ErrorHandler();
        return new \core\models\Logging\Error($container['logger']);
    };

    $container['phpErrorHandler'] = function ($container) {
        //return new ErrorHandler();
        return new \core\models\Logging\Error($container['logger']);
    };

    //add global template vars to container's setting as global vars
    $container['settings']['global_vars'] = $final_global_template_vars;

	//redirect to https if we are told to
	if(!empty($final_global_template_vars["force_ssl"])) {
		force_ssl();
	}

	//figure out what module we are in
	$current_request_data = set_current_module($app, basename(dirname($_SERVER["PHP_SELF"])), dirname($_SERVER["PHP_SELF"]));

	//create an array of all the modules
	$modules_list_array = array();
	$visible_module_count = 0;

	//need to know where all the modules are
	if(empty($final_global_template_vars["module_locations"]) || !is_array($final_global_template_vars["module_locations"])){
		$final_global_template_vars["module_locations"] = array(dirname($current_request_data["current_module_location"]));
	}
	foreach($final_global_template_vars["module_locations"] as $single_location) {
		if ($handle = opendir($single_location)) {
		    while (false !== ($entry = readdir($handle))) {
		    	$swpg_module_settings = false;
		        if ($entry != "." && $entry != ".." && is_dir($single_location . "/" . $entry)) {
		            if(is_file($single_location . "/" . $entry . "/config/settings.php") && is_file($single_location . "/" . $entry . "/config/routes.php")){
		            	//check to see if a whitelist exists, and if so, that it is in it
						$total_list_check = true;
						$white_list_check = true;
						$black_list_check = true;
						if(!empty($final_global_template_vars["module_whitelist"]) && !in_array($entry,$final_global_template_vars["module_whitelist"])){
							$white_list_check = false;
						}elseif(!empty($final_global_template_vars["module_blacklist"]) && in_array($entry,$final_global_template_vars["module_blacklist"])){
							$black_list_check = false;
						}
						if(!$white_list_check || !$black_list_check){
							$total_list_check = false;
						}

		        		if($total_list_check){
			        		require $single_location . "/" . $entry . "/config/settings.php";
							$swpg_module_settings["handle"] = $entry;
							if(empty($swpg_module_settings["sort_order"])) {
								$swpg_module_settings["sort_order"] = '100';
							}

							if(!empty($swpg_module_settings["menu_hidden"])){
								//the module is hidden, do not check if any pages are visible
							}else{
								if(!empty($swpg_module_settings["pages"]) && is_array($swpg_module_settings["pages"])){

									$swpg_module_settings["menu_hidden"] = true;
									foreach($swpg_module_settings["pages"] as $single_page){
										//check to see if the display is callable
										if(isset($single_page["display"]) && is_callable($single_page["display"])){
											$single_page["display"] = call_user_func($single_page["display"],false);
										}

										//must use isset here
										if(isset($single_page["display"]) && $single_page["display"] === false){
											//told not to display, so do nothing
										}else{
											$visible_module_count++;
											$swpg_module_settings["menu_hidden"] = false;
											break;
										}
									}
								}else{
									//if there are no pages to display, don't display the menu
									$swpg_module_settings["menu_hidden"] = true;
								}
							}
							$swpg_module_settings["absolute_path_to_this_module"] = $single_location . "/" . $entry;
							$swpg_module_settings["path_to_this_module"] = str_replace(rtrim($_SERVER["DOCUMENT_ROOT"],'/'),"",$swpg_module_settings["absolute_path_to_this_module"]);
							$modules_list_array[$entry] = array_merge($swpg_global_settings,$swpg_module_settings);
						}
		            }
		        }
		    }
		    closedir($handle);
		}
	}

	$modules_list_array = utility::subvalue_sort($modules_list_array, 'sort_order');
	$final_global_template_vars["swpg_module_list"] = $modules_list_array;
	$final_global_template_vars["visible_module_count"] = $visible_module_count;

	// Remove modules from groups entered in the "exclude_this_module_from_groups" array - added by Gor, 2014-09-25
    foreach ($final_global_template_vars["swpg_module_list"] as $single_module) {
        if (isset($single_module["exclude_this_module_from_groups"]) && isset($_SESSION[$final_global_template_vars["session_key"]]["associated_groups"]) &&
            array_intersect($single_module["exclude_this_module_from_groups"], $_SESSION[$final_global_template_vars["session_key"]]["associated_groups"])) {
            unset($final_global_template_vars["swpg_module_list"][$single_module["handle"]]);
        }
    }

	$twig = $container->get('view')->getEnvironment();
	$twig->addGlobal("swpg_module_list", $final_global_template_vars["swpg_module_list"]);
	$twig->addGlobal("visible_module_count", $final_global_template_vars["visible_module_count"]);

    if (isset($final_global_template_vars["log_page_load"]) && $final_global_template_vars["log_page_load"]) {

        $app->add(function ($request, $response, $next) {

            global $final_global_template_vars;
            $response = $next($request, $response);

            $log_params = array(
                $_SERVER["REMOTE_ADDR"]
            , $_SERVER["HTTP_USER_AGENT"]
            , $_SERVER["HTTP_HOST"]
            , $_SERVER["REQUEST_URI"]
            , isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : ""
            , (isset($_SESSION[$final_global_template_vars["session_key"]]) && isset($_SESSION[$final_global_template_vars["session_key"]]['cn'])) ? $_SESSION[$final_global_template_vars["session_key"]]['cn'] : ""
            , $final_global_template_vars["active_module"]
            );
            $log_db = new \swpg\models\db($final_global_template_vars["core_framework_db"]);
            $log_db_resource = $log_db->get_resource();
            $statement = $log_db_resource->prepare("
                        INSERT INTO page_load
                        (ip_address
                        ,http_user_agent
                        ,domain
                        ,page
                        ,created_date
                        ,referer
                        ,cn
                        ,module)
                        VALUES
                        (?,?,?,?,NOW(),?,?,?)");
            $statement->execute($log_params);
            $log_db->close_connection();

            return $response;
        });
    }

	// Run app
	$app->run();
?>