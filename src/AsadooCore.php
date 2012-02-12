<?php
final class AsadooCore extends AsadooMixin {
    private static $instance;
    private $handlers = array();
    private $interrupted = false;
    private $started = false;

    private $beforeCallback = null;
    private $afterCallback = null;

    private function __construct() {
        $this->request = new AsadooRequest($this);
        $this->response = new AsadooResponse($this);
        $this->dependences = new AsadooDependences($this);
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

    public function end() {
        $this->interrupted = true;
        $this->after();
    }

    private function match($conditions) {
        if (!count($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            if ($this->matchCondition($condition)) {
                return true;
            }
        }
        return false;
    }

    private function matchCondition($condition) {
        if (is_callable($condition) && $condition($this->request, $this->response, $this->dependences)) {
            return true;
        }

        if (is_string($condition)) {
            if (trim($condition) == '*') {
                return true;
            }
            if (trim($condition) == '/') {
                return str_replace('/', '', $this->request->path()) === '';
            }
            if ($this->matchStringCondition($condition)) {
                return true;
            }
        }

        return false;
    }

    private function formatStringCondition($condition) {
        $condition = str_replace('*', '.*?', $condition);

        if (substr($condition, -1, 1) == '/') {
            $condition = substr($condition, 0, -1) . '/?';
        }

        $condition = preg_replace('/\//', '\/', $condition) . '$';

        return $condition;
    }

    // TODO refactor
    private function matchStringCondition($condition) {
        $url = $this->request->path();

        $keys = array();

        $condition = '/^' . $this->formatStringCondition($condition) . '/';

        while (strpos($condition, ':') !== false) {
            $matches = array();

            if (preg_match('/:(\w+)/', $condition, $matches)) {
                $keys[] = $matches[1];

                $condition = preg_replace('/:\w+/', '([^\/\?\#]+)', $condition, 1);
            }
        }

        $values = array();

        $result = preg_match($condition, $url, $values);

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

    public function getBaseURL() {
        return $this->request->getBaseURL();
    }

    public function setSanitizer($fn) {
        $this->request->setSanitizer($fn);

        return $this;
    }

    public function handle($name) {
        foreach ($this->handlers as $handler) {
            if($handler->name() == $name) {
                $fn = $handler->fn;
                $fn($this->request, $this->response, $this->dependences);
            }
        }

        return $this;
    }
}

function asadoo() {
    return new AsadooFacade(AsadooCore::getInstance());
}