<?php
namespace asadoo\core;

final class Asadoo {
    private static $instance;
    public $config = array();
    private $container;

    public static function getInstance() {
        return self::$instance ? self::$instance : (self::$instance = new self);
    }

    public function __construct() {
        // Container setup
        $container = $this->container = new Container();

        $container->request = $container->asShared(function() {
                return Request::create();
            }
        );
        $container->response = $container->asShared(function() {
                return Response::create();
            }
        );
        $container->router = $container->asShared(function() use($container) {
                $instance = Router::getInstance();
                
                $instance->setRequest($container->request);
                $instance->setResponse($container->response);

                return $instance;
            }
        );
    }

    public function setConfig($config) {
        if (is_array($config)) {
            $this->config = $config;
        }
    }

    public function start() {
        $this->container->router->handle();
    }

    public function get($key, $fallback = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $fallback;
    }

    public function getContainer() {
        return $this->container;
    }
}