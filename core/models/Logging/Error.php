<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 12/7/17
 * Time: 10:39 AM
 */

namespace core\models\Logging;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use Slim\Handlers\AbstractError;
use Throwable;

final class Error extends AbstractError {

    protected $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Response $response, Throwable $exception) {
        // Log the message
        $this->logger->critical($exception->getMessage());

        $status = $exception->getCode() ?: 500;

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    }

}