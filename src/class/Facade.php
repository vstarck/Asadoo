<?php
namespace asadoo;

final class Facade {
    use Mixable;

    private $handler;

    /**
     * @var \asadoo\Core
     */
    private $core;

    /**
     * @param \asadoo\Core $core
     */
    public function __construct($core) {
        $this->core = $core;
    }

    /**
     * @return Handler
     */
    private function getHandler() {
        if (!$this->handler) {
            $this->handler = new Handler($this->core);
        }

        return $this->handler;
    }

    public function __call($name, $args) {
        $handler = $this->getHandler();

        if (method_exists($handler, $name)) {
            call_user_func_array(array($handler, $name), $args);

            return $this;
        }

        if (preg_match('/^(get|delete|post|put)$/', strtolower($name))) {
            return $this->method(strtoupper($name), 1);
        }

        return \asadoo\Mixin::__call($name, $args);
    }

    public function dependences() {
        return $this->core->dependences;
    }

    public function start() {
        $this->core->start();

        return $this;
    }

    public function method($method, $route, $fn = null) {
        $handler = $this->getHandler();

        if(is_callable($route)) {
            $fn = $route;
            $route = null;
        }

        if(!is_null($route)) {
            $handler->on($route);
        }

        $handler->handle(function($memo) use($method, $fn, $route) {
            $core = $this->core;

            if ($this->req->method() === $method && (is_null($route) || $core->matches($route))) {
               return $core->exec($fn);
            }

            return $memo;
        });

        return $this;
    }

    public function post($route, $fn = null) {
        return $this->method(Request::POST, $route, $fn);
    }

    public function get($route, $fn = null) {
        return $this->method(Request::GET, $route, $fn);
    }

    public function put($route, $fn = null) {
        return $this->method(Request::PUT, $route, $fn);
    }

    public function delete($route, $fn = null) {
        return $this->method(Request::DELETE, $route, $fn);
    }

    public function after($fn) {
        $this->core->after($fn);

        return $this;
    }

    public function before($fn) {
        $this->core->before($fn);

        return $this;
    }

    public function sanitizer($fn = null) {
        $this->core->sanitizer($fn);

        return $this;
    }

    public function name($name) {
        $this->getHandler()->name($name);

        return $this;
    }

    public function version() {
        return '0.2';
    }
}