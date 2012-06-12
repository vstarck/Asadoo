<?php
namespace asadoo;

final class Handler extends Mixin{
    public $conditions = array();
    public $fn;
    private $handlerName;

    public function on($condition) {
        $this->conditions[] = $condition;
        return $this;
    }

    public function handle($fn) {
        $this->fn = $fn;

        return $this;
    }

    public function name($name = null) {
        if(!is_null($name)) {
            $this->handlerName = $name;
            return $this;
        }

        return $this->handlerName;
    }
}