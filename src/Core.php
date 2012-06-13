<?php
namespace asadoo;

final class Core extends Mixin {
    /**
     * @var Core
     */
    private static $instance;

    /**
     * @var \asadoo\Request
     */
    private $request;

    /**
     * @var \asadoo\Response
     */
    private $response;

    /**
     * @var \asadoo\Matcher
     */
    private $matcher;

    /**
     * @var \Pimple
     */
    private $dependences;

    private $handlers = array();
    private $interrupted = false;
    private $started = false;

    private $beforeCallback = null;
    private $afterCallback = null;

    private function __construct() {
        $request = $this->request = new Request($this);
        $response = $this->response = new Response($this);
        $dependences = $this->dependences = new \Pimple();

        $this->matcher = new Matcher($this, $request, $response, $dependences);
    }

    private function __clone() {
    }

    public static function getInstance() {
        return self::$instance ? self::$instance : (self::$instance = new self());
    }

    public function add($handler) {
        $this->handlers[] = $handler;
    }

    public function start() {
        if ($this->started) {
            return;
        }

        $this->started = true;

        $this->before();

        foreach ($this->handlers as $handler) {
            if ($this->interrupted) {
                break;
            }

            if ($this->matcher->match($handler->conditions)) {
                $fn = $handler->fn;
                $fn($this->request, $this->response, $this->dependences);
            }
        }

        if (!$this->interrupted) {
            $this->response->end();
        }
    }

    public function end() {
        $this->interrupted = true;
        $this->after();
    }

    public function after($fn = null) {
        if ($fn) {
            $this->afterCallback = $fn;
        } else if (is_callable($fn = $this->afterCallback)) {
            $fn($this->request, $this->response, $this->dependences);
        }

        return $this;
    }

    public function before($fn = null) {
        if ($fn) {
            $this->beforeCallback = $fn;
        } else if (is_callable($fn = $this->beforeCallback)) {
            $fn($this->request, $this->response, $this->dependences);
        }

        return $this;
    }

    public function baseURL() {
        return $this->request->baseURL();
    }

    public function handle($name) {
        foreach ($this->handlers as $handler) {
            if($handler->name() === $name) {
                $fn = $handler->fn;
                $fn($this->request, $this->response, $this->dependences);
                break;
            }
        }

        return $this;
    }
}