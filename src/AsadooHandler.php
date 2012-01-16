<?php
class AsadooHandler {
    public $conditions = array();
    public $fn;
    public $finisher = false;

    public function on($condition) {
        $this->conditions[] = $condition;
        return $this;
    }

    public function handle($fn) {
        $this->fn = $fn;
        $this->register($this);

        return $this;
    }

    private function register($handler) {
        AsadooCore::getInstance()->add($handler);
    }
}