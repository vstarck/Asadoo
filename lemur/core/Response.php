<?php
namespace lemur\core;

/**
 * Agrupa las funcionalidades relacionadas al
 * response que se entregara al cliente
 */
class Response {
	private static $instance;

	/**
	 *
	 * @return lemur\core\Response
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

	/**
	 * Define un valor para ser utilizado en
	 * la vista
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return lemur\core\Response
	 */
	public function set($key, $value) {
		$this->viewVars[$key] = $value;
		return $this;
	}

	/**
	 *
	 * @return lemur\core\Response
	 */
	public function display() {
		if(!$this->viewName) {
			if($this->body) {
				echo $this->body;
			}
			return;
		}

		// TODO desacoplar Twig
		Lemur::loadTwig();		
		// TODO path a views en constant/config/getter?	
		$loader = new \Twig_Loader_Filesystem(BASE_PATH . DIRECTORY_SEPARATOR . 'views');
		$twig = new \Twig_Environment($loader);
		
		$template = $twig->loadTemplate($this->viewName);

		$template->display($this->viewVars);

		// TODO detener ejecucion?
		return $this;
	}

	/**
	 *
	 * @return lemur\core\Response
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
	 * @return lemur\core\Response
	 */
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}

	/**
	 * @param int $level
	 * @return lemur\core\Response
	 */
	public function setCacheControl($level) {
		// TODO
	}

	/**
	 * @param string $mime
	 * @return lemur\core\Response
	 */
	public function setMimeType($mime) {
		$this->mime = $mime;
		return $this;
	}
}