<?php
/**
 * Asadoo
 *
 * @see https://github.com/Aijoona/Asadoo
 * @author Valentin Starck
 */

namespace asadoo {
class Mixin {
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
        throw new \ErrorException('Method not found: ' . $name);
    }
}
final class Core extends Mixin {
    /**
     * @var Core
     */
    private static $instance;
    /**
     * @var \asadoo\Request
     */
    private $request;
    /**
     * @var \asadoo\Response
     */
    private $response;
    /**
     * @var \asadoo\Matcher
     */
    private $matcher;
    /**
     * @var \Pimple
     */
    public $dependences;
    private $handlers = array();
    private $interrupted = false;
    private $started = false;
    private $beforeCallback = null;
    private $afterCallback = null;
    private $sanitizer = null;
    private $memo = null;
    private function __construct() {
        $request = $this->request = new Request($this);
        $response = $this->response = new Response($this);
        $dependences = $this->dependences = new \Pimple();
        $this->matcher = new Matcher($this, $request, $response, $dependences);
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
            if ($this->matcher->match($handler->conditions)) {
                $this->exec($handler);
            }
        }
        if (!$this->interrupted) {
            $this->response->end();
        }
    }
    private function exec(Handler $handler) {
        $fn = $handler->fn;
        $this->memo = $fn(
            $this->memo,
            $this->request,
            $this->response,
            $this->dependences
        );
    }
    public function end() {
        $this->interrupted = true;
        $this->after();
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
    public function baseURL() {
        return $this->request->baseURL();
    }
    public function handle($name) {
        foreach ($this->handlers as $handler) {
            if($handler->name() === $name) {
                $this->exec($handler);
                break;
            }
        }
        return $this;
    }
    public function sanitizer($fn = null) {
        if (is_null($fn)) {
            return $this->sanitizer;
        }
        $this->sanitizer = $fn;
        return $this;
    }
    public function sanitize($value, $type) {
        return $value;
    }
}
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
final class Response extends Mixin {
    private $core;
    private $code = 200;
    private $formatters = array();
    private $output = null;
    private $codes = array(
        200 => 'OK', 201 => 'Created', 202 => 'Accepted',
        203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content',
        206 => 'Partial Content', 300 => 'Multiple Choices', 301 => 'Moved Permanently',
        302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy',
        307 => 'Temporary Redirect', 400 => 'Bad Request', 401 => 'Unauthorized',
        402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found',
        405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required',
        408 => 'Request Timeout', 409 => 'Conflict', 411 => 'Length Required',
        412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type', 416 => 'Requested Range Not Satisfiable', 417 => 'Expectation Failed',
        500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway',
        503 => 'Service Unavailable', 504 => 'Gateway Timeout', 505 => 'HTTP Version Not Supported'
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
        if(headers_sent($file, $line)) {
            throw \ErrorException("Headers already sent in $file:$line");
        }
        header($key . ' ' . $value);
        return $this;
    }
    public function write() {
        foreach (func_get_args() as $arg) {
            echo $arg;
        }
        return $this;
    }
    public function end() {
        if(count(func_get_args())) {
            call_user_func_array(array($this, 'write'), func_get_args());
        }
        $this->core->end();
        $this->sendResponseCode($this->code);
        $this->output = ob_get_clean();
        foreach ($this->formatters as $formatter) {
            if (is_callable($formatter)) {
                $this->output = $formatter($this->output);
            }
        }
        echo $this->output;
        return $this;
    }
    public function format($formatter) {
        $this->formatters[] = $formatter;
    }
}
final class Matcher extends Mixin {
    /**
     * @var Core
     */
    private $core;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var \Pimple
     */
    private $dependences;
    /**
     * @param Core $core
     * @param Request $request
     * @param Response $response
     * @param \Pimple $dependences
     */
    public function __construct($core, $request, $response, $dependences) {
        $this->core = $core;
        $this->request = $request;
        $this->response = $response;
        $this->dependences = $dependences;
    }
    /**
     * @param array $conditions
     * @return bool
     */
    public function match($conditions) {
        foreach ($conditions as $condition) {
            if ($this->matchCondition($condition)) {
                return true;
            }
        }
        return false;
    }
    /**
     * @param mixed $condition
     * @return bool
     */
    private function matchCondition($condition) {
        if (is_callable($condition) && $condition($this->request, $this->response, $this->dependences)) {
            return true;
        }
        if (is_string($condition)) {
            if (trim($condition) === '*') {
                return true;
            }
            if (trim($condition) === '/') {
                return str_replace('/', '', $this->request->path()) === '';
            }
            if ($this->matchStringCondition($condition)) {
                return true;
            }
        }
        return false;
    }
    /**
     * @param string $condition
     * @return string
     */
    private function formatStringCondition($condition) {
        $condition = str_replace('*', '.*?', $condition);
        if (substr($condition, -1, 1) === '/') {
            $condition = substr($condition, 0, -1) . '/?';
        }
        $condition = preg_replace('/\//', '\/', $condition) . '$';
        return $condition;
    }
    private function matchStringCondition($condition) {
        $keys = array();
        $condition = '/^' . $this->formatStringCondition($condition) . '/';
        while (strpos($condition, ':') !== false) {
            $matches = array();
            if (preg_match('/:(\w+)/', $condition, $matches)) {
                $keys[] = $matches[1];
                $condition = preg_replace('/:\w+/', '([^\/\?\#]+)', $condition, 1);
            }
        }
        $values = array();
        $result = preg_match($condition, $this->request->path(), $values);
        $this->setupValues($keys, $values);
        return $result;
    }
    private function setupValues($keys, $values) {
        if (count($keys)) {
            array_shift($values);
            $this->request->set(
                array_combine($keys, $values)
            );
        }
    }
}
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
final class Facade extends Mixin {
    private $handler;
    private $core;
    private $conditions = array();
    /**
     * @param Core $core
     */
    public function __construct($core) {
        $this->core = $core;
    }
    /**
     * @return Handler
     */
    private function getHandler() {
        if (!$this->handler) {
            $this->handler = new Handler($this->core);
            $this->core->add($this->handler);
        }
        return $this->handler;
    }
    public function __call($name, $arguments) {
        $handler = $this->getHandler();
        if (method_exists($handler, $name)) {
            call_user_func_array(array($handler, $name), $arguments);
            return $this;
        }
        return \asadoo\Mixin::__call($name, $arguments);
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
            if ($request->isPOST()) {
                $fn($request, $response, $dependences);
            }
        });
    }
    public function get($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isGET()) {
                $fn($request, $response, $dependences);
            }
        });
    }
    public function put($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isPUT()) {
                $fn($request, $response, $dependences);
            }
        });
    }
    public function delete($route, $fn) {
        return $this
                ->getHandler()
                ->on($route)
                ->handle(function($request, $response, $dependences) use($fn) {
            if ($request->isDELETE()) {
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
        return $this;
    }
    public function sanitizer($fn = null) {
        $this->core->sanitizer($fn);
        return $this;
    }
    public function name($name) {
        $this->getHandler()->name($name);
        return $this;
    }
    public function version() {
        return '0.2';
    }
}
} // asadoo
/*
 * This file is part of Pimple.
 *
 * Copyright (c) 2009 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace {
    /**
     * Pimple main class.
     *
     * @package pimple
     * @author  Fabien Potencier
     */
    class Pimple implements ArrayAccess {
        private $values;
        /**
         * Instantiate the container.
         *
         * Objects and parameters can be passed as argument to the constructor.
         *
         * @param array $values The parameters or objects.
         */
        function __construct(array $values = array()) {
            $this->values = $values;
        }
        /**
         * Sets a parameter or an object.
         *
         * Objects must be defined as Closures.
         *
         * Allowing any PHP callable leads to difficult to debug problems
         * as function names (strings) are callable (creating a function with
         * the same a name as an existing parameter would break your container).
         *
         * @param string $id    The unique identifier for the parameter or object
         * @param mixed  $value The value of the parameter or a closure to defined an object
         */
        function offsetSet($id, $value) {
            $this->values[$id] = $value;
        }
        /**
         * Gets a parameter or an object.
         *
         * @param  string $id The unique identifier for the parameter or object
         *
         * @return mixed  The value of the parameter or an object
         *
         * @throws InvalidArgumentException if the identifier is not defined
         */
        function offsetGet($id) {
            if (!array_key_exists($id, $this->values)) {
                throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
            }
            return $this->values[$id] instanceof Closure ? $this->values[$id]($this) : $this->values[$id];
        }
        /**
         * Checks if a parameter or an object is set.
         *
         * @param  string $id The unique identifier for the parameter or object
         *
         * @return Boolean
         */
        function offsetExists($id) {
            return array_key_exists($id, $this->values);
        }
        /**
         * Unsets a parameter or an object.
         *
         * @param  string $id The unique identifier for the parameter or object
         */
        function offsetUnset($id) {
            unset($this->values[$id]);
        }
        /**
         * Returns a closure that stores the result of the given closure for
         * uniqueness in the scope of this instance of Pimple.
         *
         * @param Closure $callable A closure to wrap for uniqueness
         *
         * @return Closure The wrapped closure
         */
        function share(Closure $callable) {
            return function ($c) use ($callable) {
                static $object;
                if (is_null($object)) {
                    $object = $callable($c);
                }
                return $object;
            };
        }
        /**
         * Protects a callable from being interpreted as a service.
         *
         * This is useful when you want to store a callable as a parameter.
         *
         * @param Closure $callable A closure to protect from being evaluated
         *
         * @return Closure The protected closure
         */
        function protect(Closure $callable) {
            return function ($c) use ($callable) {
                return $callable;
            };
        }
        /**
         * Gets a parameter or the closure defining an object.
         *
         * @param  string $id The unique identifier for the parameter or object
         *
         * @return mixed  The value of the parameter or the closure defining an object
         *
         * @throws InvalidArgumentException if the identifier is not defined
         */
        function raw($id) {
            if (!array_key_exists($id, $this->values)) {
                throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
            }
            return $this->values[$id];
        }
        /**
         * Extends an object definition.
         *
         * Useful when you want to extend an existing object definition,
         * without necessarily loading that object.
         *
         * @param  string  $id       The unique identifier for the object
         * @param  Closure $callable A closure to extend the original
         *
         * @return Closure The wrapped closure
         *
         * @throws InvalidArgumentException if the identifier is not defined
         */
        function extend($id, Closure $callable) {
            if (!array_key_exists($id, $this->values)) {
                throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
            }
            $factory = $this->values[$id];
            if (!($factory instanceof Closure)) {
                throw new InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.', $id));
            }
            return $this->values[$id] = function ($c) use ($callable, $factory) {
                return $callable($factory($c), $c);
            };
        }
        /**
         * Returns all defined value names.
         *
         * @return array An array of value names
         */
        function keys() {
            return array_keys($this->values);
        }
    }
}
namespace {
    function asadoo() {
        return new \asadoo\Facade(
            \asadoo\Core::getInstance()
        );
    }
}