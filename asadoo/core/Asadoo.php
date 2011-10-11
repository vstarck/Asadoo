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

                $instance->setRequest();
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
        $this->handle();
    }

    public function get($key, $fallback = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $fallback;
    }

    public function getContainer() {
        return $this->container;
    }

    public static function load($filepath) {
        $filepath = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $filepath);

        if (substr($filepath, 0, 1) != DIRECTORY_SEPARATOR) {
            $filepath = DIRECTORY_SEPARATOR . $filepath;
        }

        $filepath = BASE_PATH . $filepath;

        if (!file_exists($filepath)) {
            return false;
        }

        require_once($filepath);

        return true;
    }

    private $handlers = array();

    /**
     * Gestiona un request
     */
    public function handle() {
        $request = $this->container->request;
        $response = $this->container->response;
        $res = null;

        // Los handlers se activan en orden de registro
        foreach ($this->handlers as $handler) {
            if (is_callable($handler)) {
                $res = $handler($request, $response);
            } else {
                // Si el handler acepta el request lo atiende
                if ($handler->accept($request)) {
                    // Un handler puede interrumpir la ejecucion del pipeline
                    // devolviendo false
                    $res = $handler->handle($request, $response);
                }
            }

            if ($res === false) {
                break;
            }
        }
    }

    /**
     * Registar a request handler
     *
     * @throws Exception
     * @return Router
     */
    public function addHandler() {
        $args = func_get_args();

        foreach ($args as $handler) {
            if (!($handler instanceof IHandler) && !is_callable($handler)) {
                throw new Exception("Invalid argument: handler", 1);
            }
            $this->handlers[] = $handler;
        }
        return $this;
    }
}