<?php
namespace asadoo\handlers;
use \asadoo\core;
use Closure;

/**
 * new GenericPostHandler('/user/save', function($request, $response) {
 *      // stuff
 * })
 */
class GenericPostHandler implements \asadoo\core\IHandler {
    protected $path;
    protected $handler;
    
    public function __construct($path, $handler) {
        $this->path = $path;

    }

    public function accept(Request $request, Closure $container) {
        return $request->isPost() && $request->path() == $this->path;
    }

    public function handle(Request $request, Response $response, Closure $container) {
        if(is_callable($this->handler)) {
            return $handler($request, $response, $container);
        }
    }
}
