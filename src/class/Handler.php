<?php
namespace asadoo;

final class Handler {
    use Mixable;

    public $conditions = array();
    public $handlers = array();
    private $handlerName;

    public function __construct($core) {
        $core->add($this);
    }

    public function on($condition) {
        $this->conditions[] = $condition;
        return $this;
    }

    public function handle($fn) {
        $this->handlers[] = $fn;
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