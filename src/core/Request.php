<?php
namespace asadoo\core;

/**
 * @author Valentin Starck
 */
class Request {
    private $requestVars = array();
    private $postVars;
    private $getVars;
    private $cookieVars;
    private $uri;
    private $created;

    /**
     * @var bool
     */
    private $active = true;

    private static $instance;

    public static function create($deps = array()) {
        if (self::$instance) {
            return self::$instance;
        }

        $instance = new self;

        $instance->postVars = $_POST;
        $instance->getVars = $_GET;
        $instance->cookieVars = $_COOKIE;
        $instance->uri = isset($_GET['__req']) && $_GET['__req'] ? $_GET['__req'] : '/';
        $instance->created = microtime();

        unset($_POST, $_GET, $_COOKIE, $_REQUEST, $instance->getVars['__req']);

        foreach($deps as $key => $value) {
            $instance->{$key} = $value;
        }

        return self::$instance = $instance;
    }

    private function __construct() {
    }

    private function __clone() {
    }

    public function end() {
        $this->active = false;
    }

    public function isActive() {
        return $this->active;
    }

    /**
     * @param $key
     * @param mixed|null $fallback
     * @return mixed|null
     */
    public function post($key, $fallback = null) {
        return isset($this->postVars[$key]) ? $this->postVars[$key] : $fallback;
    }

    /**
     * @param $key
     * @param mixed|null $fallback
     * @return mixed|null
     */
    public function get($key, $fallback = null) {
        return isset($this->getVars[$key]) ? $this->getVars[$key] : $fallback;
    }

    /**
     * @param $key
     * @param mixed|null $fallback
     * @return mixed|null
     */
    public function cookie($key, $fallback = null) {
        return isset($this->cookieVars[$key]) ? $this->cookieVars[$key] : $fallback;
    }

    /**
     * @param int $index
     * @return string|null
     */
    public function segment($index = 0) {
        $parts = explode('/', $this->uri);

        return isset($parts[$index]) ? $parts[$index] : null;
    }

    /**
     * @return string|null
     */
    public function lastSegment() {
        $parts = explode('/', $this->uri);

        return end($parts);
    }

    /**
     * @return array
     */
    public function uriTail() {
        $parts = explode('/', $this->uri);

        array_shift($parts);

        return $parts;
    }

    /**
     * @return bool
     */
    public function any() {
        $args = func_get_args();

        if (!count($args)) {
            return false;
        }

        foreach ($args as $match) {
            if (strpos($this->uri, $match) !== false) {
                return true;
            }
        }

        return false;
    }

    public function uriContains($re) {
        return preg_match($re, $this->uri) > 0;
    }

    /**
     * Time elapsed
     *
     * @return int
     */
    public function elapsed() {
        return number_format(microtime() - $this->created, 3);
    }

    /**
     * Set/Get from request storage
     *
     * @param $key
     * @param null $value
     * @return null
     */
    public function value($key, $value = null) {
        if (is_null($value)) {
            return isset($this->requestVars[$key]) ? $this->requestVars[$key] : null;
        }

        $this->requestVars[$key] = $value;

        return $value;
    }

    public function path() {
        return preg_replace('/\?.+/', '', $this->uri);
    }

    public function isPost() {
        return count($this->postVars) > 0;
    }

    public function send($data) {
        echo $data;
    }
}