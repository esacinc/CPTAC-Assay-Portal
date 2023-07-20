<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 11/14/17
 * Time: 2:52 PM
 */

namespace site\controllers;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class SiteController {

    private $view;
    private $logger;

    public function __construct(Twig $view, LoggerInterface $logger) {
        $this->view = $view;
        $this->logger = $logger;
    }

    public function home(Request $request, Response $response, $args = []) {
        $this->logger->info("Home page action dispatched");

        //$this->view->render($response, 'index.html', array('name' => 'test'));
        return $response->withJson($request->getQueryParams());
        //return $response;
    }

}