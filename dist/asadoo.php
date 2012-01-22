<?php
/**
 * Asadoo
 *
 * @author Valentin Starck
 */
class AsadooMixin {
    private static $mixes = array();
    public static function mix($obj) {
        self::$mixes[] = $obj;
    }
    public function __call($name, $arguments) {
        $mixes = self::$mixes;
        array_unshift($arguments, $this);
        foreach($mixes as $mix) {
            if(is_object($mix) && method_exists($mix, $name)) {
                return call_user_func_array(array($mix, $name), $arguments);
            }
        }
        throw new ErrorException('Method not found: ' . $name);
    }
}
class AsadooCore extends AsadooMixin {
    private static $instance;
    private $handlers = array();
    private $interrupted = false;
    private $started = false;
    private $beforeCallback = null;
    private $afterCallback = null;
    private function __construct() {
        $this->request = new AsadooRequest($this);
        $this->response = new AsadooResponse($this);
        $this->dependences = new AsadooDependences($this);
    }
    private function __clone() {
    }
    public static function getInstance() {
        return self::$instance ? self::$instance : (self::$instance = new self());
    }
    public function add($handler) {
        $this->handlers[] = $handler;
    }
    public function start() {
        if ($this->started) {
            return;
        }
        $this->started = true;
        $this->before();
        foreach ($this->handlers as $handler) {
            if ($this->interrupted) {
                break;
            }
            if ($this->match($handler->conditions)) {
                $fn = $handler->fn;
                $fn($this->request, $this->response, $this->dependences);
            }
        }
        if (!$this->interrupted) {
            $this->response->end();
        }
    }
    public function end() {
        $this->interrupted = true;
        $this->after();
    }
    private function match($conditions) {
        if (!count($conditions)) {
            return true;
        }
        foreach ($conditions as $condition) {
            if ($this->matchCondition($condition)) {
                return true;
            }
        }
        return false;
    }
    private function matchCondition($condition) {
        if (is_callable($condition) && $condition($this->request, $this->response, $this->dependences)) {
            return true;
        }
        if (is_string($condition)) {
            if (trim($condition) == '*') {
                return true;
            }
            if ($this->matchStringCondition($condition)) {
                return true;
            }
        }
        return false;
    }
    private function formatStringCondition($condition) {
        $condition = $this->request->getBaseURL() . $condition;
        $condition = str_replace('*', '.*', $condition);
        $condition = preg_replace('/\//', '\/', $condition) . '$';
        $condition = preg_replace('~(.*)' . preg_quote('/', '~') . '~', '$1' . '/?', $condition, 1);
        return $condition;
    }
    // TODO refactor
    private function matchStringCondition($condition) {
        $url = $this->request->url();
        $keys = array();
        $condition = $this->formatStringCondition($condition);
        while (strpos($condition, ':') !== false) {
            $matches = array();
            if (preg_match('/:(\w+)/', $condition, $matches)) {
                $keys[] = $matches[1];
                $condition = preg_replace('/:\w+/', '([^\/\?\#]+)', $condition, 1);
            }
        }
        $values = array();
        $result = preg_match('/' . $condition . '/', $url, $values);
        if (!$result) {
            return false;
        }
        if (count($keys)) {
            array_shift($values);
            $this->request->set(
                array_combine($keys, $values)
            );
        }
        return true;
    }
    public function after($fn = null) {
        if ($fn) {
            $this->afterCallback = $fn;
        } else if (is_callable($fn = $this->afterCallback)) {
            $fn($this->request, $this->response, $this->dependences);
        }
        return $this;
    }
    public function before($fn = null) {
        if ($fn) {
            $this->beforeCallback = $fn;
        } else if (is_callable($fn = $this->beforeCallback)) {
            $fn($this->request, $this->response, $this->dependences);
        }
        return $this;
    }
    public function getBaseURL() {
        return $this->request->getBaseURL();
    }
    public function setSanitizer($fn) {
        $this->request->setSanitizer($fn);
        return $this;
    }
}
function asadoo() {
    return new AsadooFacade(AsadooCore::getInstance());
}
/**
 * @auhtor Fabien Potencer
 * @see http://www.slideshare.net/fabpot/dependency-injection-with-php-53
 */
class AsadooDependences extends AsadooMixin{
    protected $deps = array();
    private $core;
    public function __construct($core) {
        $this->core = $core;
    }
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
class AsadooRequest extends AsadooMixin {
    const POST = 'POST';
    const GET = 'GET';
    const VALUE = 'VALUE';
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
    public function isPost() {
        return $_SERVER['REQUEST_METHOD'] == self::POST;
    }
    public function isGet() {
        return $_SERVER['REQUEST_METHOD'] == self::GET;
    }
    public function path() {
        return str_replace($this->getBaseURL(), '', $_SERVER['REQUEST_URI']);
    }
    public function url() {
        return preg_replace('/\?.+/', '', $this->domain() . $_SERVER['REQUEST_URI']);
    }
    public function domain() {
        return $_SERVER['SERVER_NAME'];
    }
    public function agent($matches = null) {
        if(is_string($matches)) {
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
}
class AsadooResponse extends AsadooMixin {
    private $core;
    private $code = 200;
    private $formatters = array();
    private $output = null;
    private $codes = array(
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported'
    );
    public function __construct($core) {
        $this->core = $core;
        ob_start();
    }
    private function sendResponseCode($code) {
        if (isset($this->codes[$code])) {
            $this->header('HTTP/1.0', $code . ' ' . $this->codes[$code]);
            return true;
        }
        return false;
    }
    public function code($code) {
        $this->code = $code;
        return $this;
    }
    public function header($key, $value) {
        header($key . ' ' . $value);
    }
    public function send() {
        $arguments = func_get_args();
        foreach ($arguments as $arg) {
            echo $arg;
        }
    }
    public function end() {
        AsadooCore::getInstance()->end();
        $this->sendResponseCode($this->code);
        $this->output = ob_get_clean();
        foreach ($this->formatters as $formatter) {
            if (is_callable($formatter)) {
                $this->output = $formatter($this->output);
            }
        }
        echo $this->output;
    }
    public function format($formatter) {
        $this->formatters[] = $formatter;
    }
}
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
class AsadooFacade extends AsadooMixin {
    private $handler;
    private $core;
    public function __construct($core) {
        $this->core = $core;
    }
    private function getHandler() {
        if (!$this->handler) {
            $this->handler = new AsadooHandler($this->core);
        }
        return $this->handler;
    }
    public function __call($name, $arguments) {
        $handler = $this->getHandler();
        if (method_exists($handler, $name)) {
            call_user_func_array(array($handler, $name), $arguments);
            return $this;
        }
        return AsadooMixin::__call($name, $arguments);
    }
    public function dependences() {
        return $this->core->dependences;
    }
    public function start() {
        $this->core->start();
        return $this;
    }
    public function post($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isPost()) {
                $fn($request, $response, $dependences);
            }
        });
    }
    public function get($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isGet()) {
                $fn($request, $response, $dependences);
            }
        });
    }
    public function after($fn) {
        $this->core->after($fn);
        return $this;
    }
    public function before($fn) {
        $this->core->before($fn);
    }
    public function setBasePath($path) {
        $this->core->setBasePath($path);
        return $this;
    }
    public function setSanitizer($fn) {
        $this->core->setSanitizer($fn);
        return $this;
    }
    public function version() {
        return '0.2';
    }
}