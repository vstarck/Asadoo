<?php
namespace asadoo\core;

/**
 * Gestiona los requests, delegando a los handlers
 * registrados
 *
 * TODO permitir multiples pipelines?
 * TODO permitir manipulacion de pipeline a c/ handler?
 *
 * @singleton
 */
class Router {
	private static $instance;	

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self;			
		}

		return self::$instance;
	}
	
	private $request;
	private $response;
	private $handlers = array();

	/**
	 * Gestiona un request
	 */
	public function handle() {
		$request = $this->request;
		$response = $this->response;

		// Los handlers se activan en orden de registro
		foreach($this->handlers as $h) {
			// Si el handler acepta el request lo atiende
			if($h->accept($this->request)) {
				// Un handler puede interrumpir la ejecucion del pipeline
				// devolviendo false
				if($h->handle($request, $response) === false) {
					break;
				}
			}
		}
		// TODO mover a un handler
		$this->response->display();
	}

	/**
	 * Registra un handler
     * 
     * @throws Exception
     * @return Router
     */
	public function addHandler() {
		$args = func_get_args();
		
		foreach($args as $handler) {
			if(!($handler instanceof IHandler)) {
				throw new Exception("Invalid argument: handler", 1);				
			}		
			$this->handlers[] = $handler;
		}
		return $this;
	}

    public function setRequest($request) {
        $this->request = $request;
    }
    public function setResponse($response) {
        $this->response = $response;
    }
}