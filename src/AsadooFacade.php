<?php
class AsadooFacade {
    private $handler;

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
        return AsadooCore::getInstance()->dependences;
    }

    public function start() {
        AsadooCore::getInstance()->start();
        return $this;
    }

    public function post($route, $fn) {
        return $this
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
                    if($request->isPost()) {
                        $fn($request, $response, $dependences);
                    }
                });
    }

    public function get($route, $fn) {
        return $this
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
                    if($request->isGet()) {
                        $fn($request, $response, $dependences);
                    }
                });
    }
}
