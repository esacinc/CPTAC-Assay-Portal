<?php

namespace users\controllers;
use Slim\Http\Request;
use Slim\Http\Response;

use users\models\UserAccountDao;

use core\controllers\Controller;

class BrowseUserAccounts extends Controller {

function browse_user_accounts(Request $request, Response $response, $args = []){

	$view = $this->container->get('view');
	        $view->render(
	            $response
	            ,'browse_user_accounts.twig'
	        );

	        return $response;
}
}
?>
