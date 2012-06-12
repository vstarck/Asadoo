<?php
namespace asadoo;

final class Facade extends Mixin {
    private $handler;
    private $core;

    /**
     * @param Core $core
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
            $this->core->add($this->handler);
        }

        return $this->handler;
    }

    public function __call($name, $arguments) {
        $handler = $this->getHandler();

        if (method_exists($handler, $name)) {
            call_user_func_array(array($handler, $name), $arguments);

            return $this;
        }

        return \asadoo\Mixin::__call($name, $arguments);
    }

    public function dependences() {
        return $this->core->dependences;
    }

    public function start() {
        $this->core->start();

        return $this;
    }

    public function post($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isPOST()) {
                $fn($request, $response, $dependences);
            }
        });
    }

    public function get($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isGET()) {
                $fn($request, $response, $dependences);
            }
        });
    }

    public function put($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isPUT()) {
                $fn($request, $response, $dependences);
            }
        });
    }

    public function delete($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isDELETE()) {
                $fn($request, $response, $dependences);
            }
        });
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
        $this->core->request->sanitizer($fn);

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