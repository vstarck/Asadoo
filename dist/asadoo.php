<?php
/**
 * Asadoo
 *
 * @see https://github.com/Aijoona/Asadoo
 * @author Valentin Starck
 */
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
    class Pimple implements ArrayAccess {
        private $values;
        function __construct(array $values = array()) {
            $this->values = $values;
        }
        function offsetSet($id, $value) {
            $this->values[$id] = $value;
        }
        function offsetGet($id) {
            if (!array_key_exists($id, $this->values)) {
                throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
            }
            return $this->values[$id] instanceof Closure ? $this->values[$id]($this) : $this->values[$id];
        }
        function offsetExists($id) {
            return array_key_exists($id, $this->values);
        }
        function offsetUnset($id) {
            unset($this->values[$id]);
        }
        function share(Closure $callable) {
            return function ($c) use ($callable) {
                static $object;
                if (is_null($object)) {
                    $object = $callable($c);
                }
                return $object;
            };
        }
        function protect(Closure $callable) {
            return function ($c) use ($callable) {
                return $callable;
            };
        }
        function raw($id) {
            if (!array_key_exists($id, $this->values)) {
                throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
            }
            return $this->values[$id];
        }
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
        function keys() {
            return array_keys($this->values);
        }
    }
}
namespace asadoo {
trait Mixable {
    private static $mixes = array();
    public static function mix($obj) {
       self::$mixes[] = $obj;
    }
    public function __call($name, $arguments) {
        array_unshift($arguments, $this);
        foreach(self::$mixes as $mix) {
            if(is_object($mix) && method_exists($mix, $name)) {
                return call_user_func_array(array($mix, $name), $arguments);
            }
        }
        throw new \ErrorException('Method not found: ' . $name);
    }
}
final class Core {
    use Mixable;
    private static $instance;
    private $request;
    private $response;
    private $matcher;
    private $executionContext;
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
        $this->executionContext = new ExecutionContext($request, $response);
        $this->matcher = new Matcher($this, $this->executionContext);
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
                $this->execHandler($handler);
            }
        }
        if (!$this->interrupted) {
            $this->response->end();
        }
    }
    private function execHandler(Handler $handler) {
        foreach ($handler->handlers as $fn) {
            $this->memo = $this->exec($fn);
        }
    }
    private function fillArguments($fn, $arguments = array()) {
        if(!is_callable($fn)) {
            return $arguments;
        }
        $reflection = new \ReflectionFunction($fn);
        $names = $reflection->getParameters();
        array_shift($names);
        foreach ($names as $arg) {
            $arguments[] = $this->request->value(
                $arg->getName(),
                $arg->isOptional() ? $arg->getDefaultValue() : null
            );
        }
        return $arguments;
    }
    public function exec($fn) {
        $arguments = $this->fillArguments($fn, array($this->memo));
        return call_user_func_array(\Closure::bind(
            $fn,
            $this->executionContext,
            $this->executionContext
        ), $arguments);
    }
    public function end() {
        $this->interrupted = true;
        $this->after();
    }
    public function after($fn = null) {
        if ($fn) {
            $this->afterCallback = $fn;
        } else if (is_callable($fn = $this->afterCallback)) {
            $this->memo = $this->exec($fn);
        }
        return $this;
    }
    public function before($fn = null) {
        if ($fn) {
            $this->beforeCallback = $fn;
        } else if (is_callable($fn = $this->beforeCallback)) {
            $this->memo = $this->exec($fn);
        }
        return $this;
    }
    public function baseURL() {
        return $this->request->baseURL();
    }
    public function handle($name, $memo = null) {
        if(!is_null($memo)) {
            $this->memo = $memo;
        }
        foreach ($this->handlers as $handler) {
            if ($handler->name() === $name) {
                return $this->exec($handler);
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
    public function matches($condition) {
        return $this->matcher->matchCondition($condition);
    }
    public function result() {
        return $this->memo;
    }
}
final class Request {
    use Mixable;
    const POST = 'POST';
    const GET = 'GET';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const VALUE = 'VALUE';
    const HTTP = 'HTTP';
    const HTTPS = 'HTTPS';
    private $core;
    private $variables = array();
    public function __construct($core) {
        $this->core = $core;
    }
    public function matches($match) {
        return preg_match($match, $this->url()) !== false;
    }
    public function value($key, $fallback = null) {
        if (isset($this->variables[$key])) {
            return $this->sanitize($this->variables[$key], self::VALUE);
        }
        return $fallback;
    }
    public function post($key, $fallback = null) {
        if (isset($_POST[$key])) {
            return $this->sanitize($_POST[$key], self::POST);
        }
        return $fallback;
    }
    public function get($key, $fallback = null) {
        if (isset($_GET[$key])) {
            return $this->sanitize($_GET[$key], self::GET);
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
        return empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? self::HTTP : self::HTTPS;
    }
    public function isHTTPS() {
        return $this->scheme() == self::HTTPS;
    }
    public function forward($name, $memo = null) {
        $this->core->handle($name, $memo);
    }
}
final class Response {
    use Mixable;
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
        return $this;
    }
}
final class Matcher {
    use Mixable;
    private $core;
    private $executionContext;
    private $request;
    public function __construct($core, $executionContext) {
        $this->core = $core;
        $this->executionContext = $executionContext;
        $this->request = $executionContext->request;
    }
    public function match($conditions) {
        foreach ($conditions as $condition) {
            if ($this->matchCondition($condition)) {
                return true;
            }
        }
        return false;
    }
    public function matchCondition($condition) {
        if (is_callable($condition)) {
            return $this->core->exec($condition);
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
    private function formatStringCondition($condition) {
        $condition = str_replace('*', '.*?', $condition);
        if (substr($condition, -1, 1) === '/') {
            $condition = substr($condition, 0, -1) . '/?';
        } else {
            $condition .= '/?';
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
        if ($result) {
            $this->setupValues($keys, $values);
        }
        return $result;
    }
    private function setupValues($keys, $values) {
        array_shift($values);
        if (count($keys) && count($values) === count($keys)) {
            $this->request->set(array_combine($keys, $values));
        }
    }
}
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
final class Facade {
    use Mixable;
    private $handler;
    private $core;
    public function __construct($core) {
        $this->core = $core;
    }
    private function getHandler() {
        if (!$this->handler) {
            $this->handler = new Handler($this->core);
        }
        return $this->handler;
    }
    public function __call($name, $args) {
        $handler = $this->getHandler();
        if (method_exists($handler, $name)) {
            call_user_func_array(array($handler, $name), $args);
            return $this;
        }
        if (preg_match('/^(get|delete|post|put)$/', strtolower($name))) {
            return $this->method(strtoupper($name), 1);
        }
        return \asadoo\Mixin::__call($name, $args);
    }
    public function dependences() {
        return $this->core->dependences;
    }
    public function start() {
        $this->core->start();
        return $this;
    }
    public function method($method, $route, $fn = null) {
        $handler = $this->getHandler();
        if(is_callable($route)) {
            $fn = $route;
            $route = null;
        }
        if(!is_null($route)) {
            $handler->on($route);
        }
        $handler->handle(function($memo) use($method, $fn, $route) {
            $core = $this->core;
            if ($this->req->method() === $method && (is_null($route) || $core->matches($route))) {
               return $core->exec($fn);
            }
            return $memo;
        });
        return $this;
    }
    public function post($route, $fn = null) {
        return $this->method(Request::POST, $route, $fn);
    }
    public function get($route, $fn = null) {
        return $this->method(Request::GET, $route, $fn);
    }
    public function put($route, $fn = null) {
        return $this->method(Request::PUT, $route, $fn);
    }
    public function delete($route, $fn = null) {
        return $this->method(Request::DELETE, $route, $fn);
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
final class ExecutionContext extends \Pimple {
    use Mixable;
    public function __construct($req, $res) {
        $this->req = $this->request = $req;
        $this->res = $this->response = $res;
    }
}

} // asadoo
namespace {
    function asadoo($instance = null) {
        return new \asadoo\Facade(
            is_null($instance) ? \asadoo\Core::getInstance(): $instance
        );
    }
}