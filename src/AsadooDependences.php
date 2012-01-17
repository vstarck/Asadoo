<?php
/**
 * @auhtor Fabien Potencer
 * @see http://www.slideshare.net/fabpot/dependency-injection-with-php-53
 */
class AsadooDependences {
    protected $deps = array();

    public function register($id, $value) {
        $this->deps[$id] = $value;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function __get($id) {
        if (!isset($this->deps[$id])) {
            return null;
        }

        if (is_callable($this->deps[$id])) {
            // Lazy loading
            return $this->deps[$id]($this);
        } else {
            return $this->deps[$id];
        }
    }

    public function asShared($callable) {
        return function ($c) use ($callable) {
            static $object;

            if (is_null($object)) {
                $object = $callable($c);
            }
            return $object;
        };
    }
}