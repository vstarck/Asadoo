<?php
namespace asadoo\core;

/**
 * Agrupa las funcionalidades relacionadas al
 * response que se entregara al cliente
 */
class Response {
	private static $instance;

	/**
	 *
	 * @return asadoo\core\Response
	 */	
	public static function create() {	
		if(self::$instance) {
			return self::$instance;
		}
	
		$instance = new self;		

		return self::$instance = $instance;
	}

	private $viewVars = array();
	private $viewName = '';
	private $mime = 'text/html';
	private $textBody = '';

	/**
	 * Define un valor para ser utilizado en
	 * la vista
	 *
	 * @param string $key
	 * @param mixed|null $value
	 * @return mixed|null
	 */
	public function value($key, $value = null) {
        if(!is_null($value)) {
            $this->viewVars[$key] = $value;
        }

		return isset($this->viewVars[$key]) ? $this->viewVars[$key] : null;
	}

	/**
	 *
	 * @return asadoo\core\Response
	 */
	public function display() {
		if($this->viewName) {
			$this->textBody = $this->digestView($this->viewName);	
		}
		
		if($this->textBody) {
			header('Content-Type:', $this->mime);
			if($this->textBody) {
				echo $this->textBody;
			}
		}

		return $this;
	}

    /**
     * @param $viewName
	 * @return asadoo\core\Response
     */
	public function setView($viewName) {
		$this->viewName = $viewName;
		return $this;
	}

	public function show404() {
		echo '404';
	}

	/**
	 * @param string $body
	 * @return asadoo\core\Response
	 */
	public function setBody($body) {
		$this->textBody = $body;
		return $this;
	}

	/**
	 * @param int $level
	 * @return asadoo\core\Response
	 */
	public function setCacheControl($level) {
		// TODO
	}

	/**
	 * @param string $mime
	 * @return asadoo\core\Response
	 */
	public function setMimeType($mime) {
		$this->mime = $mime;
		return $this;
	}
	
	// TODO remove
	public function digestView($viewName) {
		foreach($this->viewVars as $key => $value) {
			$$key = $value;
		}
		
		ob_start();
		include($viewName);
		return ob_get_clean();
	}
}