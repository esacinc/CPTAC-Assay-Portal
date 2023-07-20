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

    $container['errorHandler'] = function ($container) {
        //return new ErrorHandler();
        return new \core\models\Logging\Error($container['logger']);
    };

    $container['phpErrorHandler'] = function ($container) {
        //return new ErrorHandler();
        return new \core\models\Logging\Error($container['logger']);
    };


    //$app->run();
