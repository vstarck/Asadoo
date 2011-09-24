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
	private $textBody = '';

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
			if($this->textBody) {
				echo $this->textBody;
			}
			return;
		}

		$viewPath = \lemur\core\Lemur::get('views_path', PROJECT_PATH . DIRECTORY_SEPARATOR . 'views');
		
		if(!is_dir($viewPath) || !file_exists($viewPath . DIRECTORY_SEPARATOR . $this->viewName)) {
			return $this;
		}
		
		// TODO desacoplar Twig
		Lemur::loadTwig();		
		$loader = new \Twig_Loader_Filesystem($viewPath);
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
		$this->textBody = $body;
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