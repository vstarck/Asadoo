<?php
namespace asadoo;

final class Request extends Mixin {
    const POST = 'POST';
    const GET = 'GET';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    const VALUE = 'VALUE';
    const HTTP = 'HTTP';
    const HTTPS = 'HTTPS';

    /**
     * @var Core
     */
    private $core;
    private $variables = array();

    public function __construct($core) {
        $this->core = $core;
    }

    /**
     * @param string $match
     * @return bool
     */
    public function matches($match) {
        return preg_match($match, $this->url()) !== false;
    }

    /**
     * @param string $key
     * @param mixed|null $fallback
     * @return mixed
     */
    public function value($key, $fallback = null) {
        if (isset($this->variables[$key])) {
            return $this->sanitize($this->variables[$key], self::VALUE);
        }

        return $fallback;
    }

    /**
     * @param string $key
     * @param mixed|null $fallback
     * @return mixed
     */
    public function post($key, $fallback = null) {
        if (isset($_POST[$key])) {
            return $this->sanitize($_POST[$key], self::POST);
        }

        return $fallback;
    }

    /**
     * @param string $key
     * @param mixed|null $fallback
     * @return mixed
     */
    public function get($key, $fallback = null) {
        if (isset($_GET[$key])) {
            return $this->sanitize($_GET[$key], self::GET);
        }

        return $fallback;
    }

    /**
     * @param string|array $key
     * @param mixed $value
     * @return Request
     */
    public function set($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
            return $this;
        }

        $this->variables[$key] = $value;

        return $this;
    }

    public function path() {
        $path = str_replace($this->baseURL(), '', $_SERVER['REQUEST_URI']);

        return preg_replace('/\?.+/', '', $path);
    }

    public function url() {
        return preg_replace('/\?.+/', '', $this->domain() . $_SERVER['REQUEST_URI']);
    }

    public function domain() {
        return $_SERVER['SERVER_NAME'];
    }

    public function agent($matches = null) {
        if (is_string($matches)) {
            return preg_match($matches, $this->agent());
        }

        return $_SERVER['HTTP_USER_AGENT'];
    }

    public function segment($index) {
        $parts = explode('/', $this->path());
        array_shift($parts);

        return isset($parts[$index]) ? $parts[$index] : null;
    }

    public function sanitize($value, $type = null) {
        return $this->core->sanitize($value, $type);
    }

    /**
     * @see https://github.com/codeguy/Slim/blob/master/Slim/Http/Uri.php#L69
     * @static
     * @return string
     */
    public static function baseURL() {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $baseUri = strpos($requestUri, $scriptName) === 0 ? $scriptName : str_replace('\\', '/', dirname($scriptName));

        return rtrim($baseUri, '/');
    }

    public function ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    public function port() {
        return $_SERVER['SERVER_PORT'];
    }

    public function method($is = null) {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isPOST() {
        return $this->method() == self::POST;
    }

    public function isGET() {
        return $this->method() == self::GET;
    }

    public function isPUT() {
        return $this->method() == self::PUT;
    }

    public function isDELETE() {
        return $this->method() == self::DELETE;
    }

    public function scheme() {
        return empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'off' ? self::HTTP : self::HTTPS;
    }

    public function isHTTPS() {
        return $this->scheme() == self::HTTPS;
    }

    public function forward($name) {
        $this->core->handle($name);
    }
}