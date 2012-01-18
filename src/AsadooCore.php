<?php
class AsadooCore extends AsadooMixin{
    private static $instance;
    private $handlers = array();
    private $interrupted = false;
    private $started = false;
    private $basePath = '';

    private $beforeCallback = null;
    private $afterCallback = null;

    private function __construct() {
        $this->createRequest();
        $this->createResponse();
        $this->createDependences();
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

            if ($this->match($handler->conditions)) {
                $fn = $handler->fn;
                $fn($this->request, $this->response, $this->dependences);
            }
        }

        if (!$this->interrupted) {
            $this->response->end();
        }
    }

    private function createRequest() {
        $this->request = new AsadooRequest();
    }

    private function createResponse() {
        $this->response = new AsadooResponse();
    }

    private function createDependences() {
        $this->dependences = new AsadooDependences();
    }

    public function end() {
        $this->interrupted = true;
        $this->after();
    }

    private function match($conditions) {
        foreach ($conditions as $condition) {
            if ($this->matchCondition($condition)) {
                return true;
            }
        }
        return false;
    }

    private function matchCondition($condition) {
        $request = $this->request;
        $response = $this->response;
        $dependences = $this->dependences;

        if (is_callable($condition)) {
            if ($condition($request, $response, $dependences)) {
                return true;
            }
        }

        if (is_string($condition)) {
            if (trim($condition) == '*') {
                return true;
            }

            if ($this->matchStringCondition($condition)) {
                return true;
            }
        }

        return false;
    }

    // TODO refactor
    private function matchStringCondition($condition) {
        $url = $this->request->url();

        $keys = array();

        $condition = $this->basePath . $condition;
        $condition = str_replace('*', '.*', $condition);
        $condition = preg_replace('/\//', '\/', $condition) . '$';
        $condition = preg_replace('~(.*)' . preg_quote('/', '~') . '~', '$1' . '/?', $condition, 1);

        while (strpos($condition, ':') !== false) {
            $matches = array();

            if (preg_match('/:(\w+)/', $condition, $matches)) {
                $keys[] = $matches[1];

                $condition = preg_replace('/:\w+/', '([^\/\?\#]+)', $condition, 1);
            }
        }

        $values = array();

        $result = preg_match('/' . $condition . '/', $url, $values);

        if (!$result) {
            return false;
        }

        if (count($keys)) {
            array_shift($values);

            $this->request->set(
                array_combine($keys, $values)
            );
        }

        return true;
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

    public function setBasePath($path) {
        $this->basePath = $path;
        return $this;
    }

    public function getBasePath() {
        return $this->basePath;
    }
}

function asadoo() {
    return new AsadooFacade();
}