<?php

namespace core\controllers;

/**
 *
 */
class Controller {

    protected $container;
    protected $logger;

    public function __construct($container) {
        $this->container = $container;
        $this->logger = $container->get('logger');
    }

    public function __get($property) {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }

}