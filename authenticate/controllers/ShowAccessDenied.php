<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 4/6/18
 * Time: 11:45 AM
 */

namespace authenticate\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use \core\controllers\Controller;

class ShowAccessDenied extends Controller {

    function show_access_denied(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        return $response->withRedirect($final_global_template_vars["login_url"]);
    }

}