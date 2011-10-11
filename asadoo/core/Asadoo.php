<?php
namespace asadoo\core;

final class Asadoo {
    private static $instance;
    public $config = array();


    public static function getInstance() {
        return self::$instance ? self::$instance : (self::$instance = new self);
    }

    public function setup() {
        // Container setup
        $container = $this;

        $this->register(
            'cache',
            $container->asShared(function() {
                    return \asadoo\dependences\FileCache::getInstance();
                }
            )
        );
    }

    public function setConfig($config) {
        if (is_array($config)) {
            $this->config = $config;
        }
        return $this;
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
    public function start() {
        $asadoo = $this;
        
        $this->setup();

        $request = Request::create();
        $response = Response::create();

        $container = function($dep) use($asadoo) {
            return $asadoo->$dep;
        };
        $res = null;

        // Los handlers se activan en orden de registro
        foreach ($this->handlers as $handler) {
            if (is_callable($handler)) {
                $res = $handler($request, $response, $container);
            } else {
                // Si el handler acepta el request lo atiende
                if ($handler->accept($request, $container)) {
                    // Un handler puede interrumpir la ejecucion del pipeline
                    // devolviendo false
                    $res = $handler->handle($request, $response, $container);
                }
            }

            if ($res === false) {
                break;
            }
        }
    }

    /**
     * Register a request handler
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

    /**
     * @auhtor Fabien Potencer
     * @see http://www.slideshare.net/fabpot/dependency-injection-with-php-53
     */

    protected $deps = array();

    public function register($id, $value) {
        $this->deps[$id] = $value;
    }

    /**
     * @throws InvalidArgumentException
     * @param $id
     * @return mixed
     */
    public function __get($id) {
        if (!isset($this->deps[$id])) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not defined.', $id));
        }
        if (is_callable($this->deps[$id])) {
            return $this->deps[$id]($this);
        } else {
            return $this->deps[$id];
        }
    }

    public function asShared($callable) {
        return function ($c) use ($callable) {
            static $object;

            if (is_null($object)) {
                $object = $callable($c);
            }
            return $object;
        };
    }
}