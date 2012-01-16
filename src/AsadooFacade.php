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
}
