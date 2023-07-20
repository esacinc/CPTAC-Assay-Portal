<?php
namespace public_upload\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

class CheckImportLogId {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    function __invoke(Request $request, Response $response, $next) {
        global $final_global_template_vars;

        if(!isset($_SESSION[$final_global_template_vars["session_key"]])) {
            //set cookie so user can come back to this page
            setcookie($final_global_template_vars["redirect_cookie_key"], $_SERVER["REQUEST_URI"], time()+3600, "/", false,
                $final_global_template_vars["request_https"], true);
            return $response->withRedirect($final_global_template_vars["login_url"]);
        } else {
            $session_key = $final_global_template_vars["session_key"];
            $session_id = $_SESSION[$session_key];
            $import_log_id = $_SESSION[$session_key]['import_log_id'];
            if(empty($import_log_id)) {
                return $response->withRedirect($final_global_template_vars["public_upload"]);
            }
        }

        return $next($request, $response);

    }
}