<?php
class AsadooHandler extends AsadooMixin{
    public $conditions = array();
    public $fn;
    public $finisher = false;
    private $core;

    public function __construct($core) {
        $this->core = $core;
    }

    public function on($condition) {
        $this->conditions[] = $condition;
        return $this;
    }

    public function handle($fn) {
        $this->fn = $fn;
        $this->register();

        return $this;
    }

    private function register() {
        $this->core->add($this);
    }
}