<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 7/18/18
 * Time: 3:51 PM
 */

namespace core\controllers;

class ErrorHandler {

    public function __invoke($request, $response, $exception) {

        $status = $exception->getCode() ?: 500;

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    }

}