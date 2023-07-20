<?php

namespace group\controllers;
use Slim\Http\Request;
use Slim\Http\Response;

use group\models\GroupDao;

use core\controllers\Controller;

class BrowseGroups extends Controller {
    function browse_groups(Request $request, Response $response, $args = []){
				$view = $this->container->get('view');
				$view->render($response
										,'browse_groups.twig'
								     );
			  return $response;
			}
}
