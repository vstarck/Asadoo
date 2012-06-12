<?php
namespace asadoo;

final class Handler extends Mixin{
    public $conditions = array();
    public $fn;
    public $finisher = false;
    private $core;
    private $handlerName;

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

    public function name($name = null) {
        if(!is_null($name)) {
            $this->handlerName = $name;
            return $this;
        }

        return $this->handlerName;
    }
}