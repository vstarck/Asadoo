<?php
namespace asadoo\handlers;
use \asadoo\core;

class GenericPostHandler implements \asadoo\core\IHandler {
    protected $path;
    protected $handler;
    
    public function __construct($path, $handler) {
        $this->path = $path;

    }

    public function accept(Request $request) {
        return $request->isPost() && $request->path() == $this->path;
    }

    public function handle(Request $request, Response $response) {
        if(is_callable($this->handler)) {
            return $handler($request, $response);
        }
    }
}
