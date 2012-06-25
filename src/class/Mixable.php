<?php
namespace asadoo;

trait Mixable {
    private static $mixes = array();

    public static function mix($obj) {
       self::$mixes[] = $obj;
    }

    public function __call($name, $arguments) {
        array_unshift($arguments, $this);

        foreach(self::mixes as $mix) {
            if(is_object($mix) && method_exists($mix, $name)) {
                return call_user_func_array(array($mix, $name), $arguments);
            }
        }

        throw new \ErrorException('Method not found: ' . $name);
    }
}