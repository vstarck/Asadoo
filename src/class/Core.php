<?php
namespace asadoo;

final class Core {
use Mixable;

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
     * @var \asadoo\ExecutionContext
     */
    private $executionContext;

    private $handlers = array();
    private $interrupted = false;
    private $started = false;

    private $beforeCallback = null;
    private $afterCallback = null;

    private $sanitizer = null;
    private $memo = null;

    private function __construct() {
        $request = $this->request = new Request($this);
        $response = $this->response = new Response($this);
        $executionContext = $this->executionContext = new ExecutionContext($request, $response);

        $this->matcher = new Matcher($this, $executionContext);
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
                $this->execHandler($handler);
            }
        }

        if (!$this->interrupted) {
            $this->response->end();
        }
    }

    private function execHandler(Handler $handler) {
        foreach ($handler->handlers as $fn) {
            $this->memo = $this->exec($fn);
        }
    }

    private function fillArguments($fn, $arguments = array()) {
        $reflection = new \ReflectionFunction($fn);
        $names = $reflection->getParameters();

        array_shift($names);

        foreach ($names as $arg) {
            $arguments[] = $this->request->value(
                $arg->getName(),
                $arg->isOptional() ? $arg->getDefaultValue() : null
            );
        }

        return $arguments;
    }

    public function exec($fn) {
        $arguments = $this->fillArguments($fn, array($this->memo));

        return call_user_func_array(\Closure::bind(
            $fn,
            $this->executionContext,
            $this->executionContext
        ), $arguments);
    }

    public function end() {
        $this->interrupted = true;
        $this->after();
    }

    public function after($fn = null) {
        if ($fn) {
            $this->afterCallback = $fn;
        } else if (is_callable($fn = $this->afterCallback)) {
            $this->memo = $this->exec($fn);
        }

        return $this;
    }

    public function before($fn = null) {
        if ($fn) {
            $this->beforeCallback = $fn;
        } else if (is_callable($fn = $this->beforeCallback)) {
            $this->memo = $this->exec($fn);
        }

        return $this;
    }

    public function baseURL() {
        return $this->request->baseURL();
    }

    public function handle($name) {
        foreach ($this->handlers as $handler) {
            if ($handler->name() === $name) {
                $this->exec($handler);
                break;
            }
        }

        return $this;
    }

    public function sanitizer($fn = null) {
        if (is_null($fn)) {
            return $this->sanitizer;
        }

        $this->sanitizer = $fn;

        return $this;
    }

    public function sanitize($value, $type) {
        return $value;
    }

    public function matches($condition) {
        return $this->matcher->matchCondition($condition);
    }

    public function result() {
        return $this->memo;
    }
}