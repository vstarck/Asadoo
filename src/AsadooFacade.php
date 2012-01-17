<?php
class AsadooFacade {
    private $handler;
    private $core;

    public function __construct() {
        $this->core = AsadooCore::getInstance();
    }

    private function getHandler() {
        if (!$this->handler) {
            $this->handler = new AsadooHandler();
        }

        return $this->handler;
    }

    public function __call($name, $arguments) {
        $handler = $this->getHandler();

        call_user_func_array(array($handler, $name), $arguments);

        return $this;
    }

    public function dependences() {
        return $this->core->dependences;
    }

    public function start() {
        AsadooCore::getInstance()->start();
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
}
