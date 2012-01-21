<?php
class AsadooFacade extends AsadooMixin {
    private $handler;
    private $core;

    public function __construct($core) {
        $this->core = $core;
    }

    private function getHandler() {
        if (!$this->handler) {
            $this->handler = new AsadooHandler($this->core);
        }

        return $this->handler;
    }

    public function __call($name, $arguments) {
        $handler = $this->getHandler();

        if (method_exists($handler, $name)) {
            call_user_func_array(array($handler, $name), $arguments);

            return $this;
        }

        return AsadooMixin::__call($name, $arguments);
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
            if ($request->isPost()) {
                $fn($request, $response, $dependences);
            }
        });
    }

    public function get($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isGet()) {
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
    }

    public function setBasePath($path) {
        $this->core->setBasePath($path);
        return $this;
    }

    public function setSanitizer($fn) {
        $this->core->setSanitizer($fn);
        return $this;
    }

    public function version() {
        return '0.2';
    }
}