<?php
final class AsadooRequest extends AsadooMixin {
    const POST = 'POST';
    const GET = 'GET';
    const VALUE = 'VALUE';
    const HTTP = 'HTTP';
    const HTTPS = 'HTTPS';

    /**
     * @var AsadooCore
     */
    private $core;
    private $variables = array();
    private $sanitizer;

    public function __construct($core) {
        $this->core = $core;
    }

    public function has($match) {
        return strpos($this->url(), $match) !== false;
    }

    public function value($key, $fallback = null) {
        if (isset($this->variables[$key])) {
            return $this->sanitize($this->variables[$key], self::VALUE, $this->core->dependences);
        }

        return $fallback;
    }

    public function post($key, $fallback = null) {
        if (isset($_POST[$key])) {
            return $this->sanitize($_POST[$key], self::POST, $this->core->dependences);
        }

        return $fallback;
    }

    public function get($key, $fallback = null) {
        if (isset($_GET[$key])) {
            return $this->sanitize($_GET[$key], self::GET, $this->core->dependences);
        }

        return $fallback;
    }

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
        $path = str_replace($this->getBaseURL(), '', $_SERVER['REQUEST_URI']);

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
        $parts = explode('/', $_SERVER['REQUEST_URI']);
        array_shift($parts);

        return isset($parts[$index]) ? $parts[$index] : null;
    }

    private function sanitize($value, $type, $dependences) {
        if (is_callable($fn = $this->sanitizer)) {
            return $fn($value, $type, $dependences);
        }

        return $value;
    }

    public function setSanitizer($fn) {
        $this->sanitizer = $fn;

        return $this;
    }

    /**
     * @see https://github.com/codeguy/Slim/blob/master/Slim/Http/Uri.php#L69
     * @static
     * @return string
     */
    public static function getBaseURL() {
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

    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isPost() {
        return $this->method() == self::POST;
    }

    public function isGet() {
        return $this->method() == self::GET;
    }

    public function scheme() {
        return empty($_SERVER['HTTPS']) ? self::HTTP : self::HTTPS;
    }

    public function isHttps() {
        return $this->scheme() == self::HTTPS;
    }

    public function forward($name) {
        $this->core->handle($name);
    }
}