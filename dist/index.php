<?php
namespace asadoo;
use Closure;
use Exception;



final class Asadoo {
    private static $instance;

    public static function getInstance() {
        return self::$instance ? self::$instance : (self::$instance = new self);
    }

    private function __construct() {
        // Container setup
        $container = $this;

        // Lazy dependence syntax
        $this->register(
            'file_cache',
            $container->asShared(function() {
                    return \asadoo\FileCache::getInstance();
                }
            )
        );
        $this->register(
            'config',
            $container->asShared(function() {
                    return new \asadoo\Config;
                }
            )
        );
    }

    public function setConfig($config) {
        $this->config->set($config);
        return $this;
    }

    public static function load($filepath) {
        $filepath = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $filepath);

        if (substr($filepath, 0, 1) != DIRECTORY_SEPARATOR) {
            $filepath = DIRECTORY_SEPARATOR . $filepath;
        }

        $filepath = BASE_PATH . $filepath;

        if (!file_exists($filepath)) {
            return false;
        }

        require_once($filepath);

        return true;
    }

    private $handlers = array();

    /**
     * Gestiona un request
     */
    public function start() {
        $asadoo = $this;

        $request = Request::create();
        $response = Response::create();

        $container = function($dep) use($asadoo) {
            return $asadoo->$dep;
        };

        $res = null;

        // Los handlers se activan en orden de registro
        foreach ($this->handlers as $handler) {
            if (is_callable($handler)) {
                $res = $handler($request, $response, $container);
            } else {
                // Si el handler acepta el request lo atiende
                if ($handler->accept($request, $container)) {
                    // Un handler puede interrumpir la ejecucion del pipeline
                    // devolviendo false
                    $res = $handler->handle($request, $response, $container);
                }
            }

            if (is_string($res)) {
                $request->send($res);
            }
            if ($res === false || !$request->isActive()) {
                break;
            }
        }
    }

    /**
     * Register a request handler
     *
     * @throws Exception
     * @return Router
     */
    public function addHandler() {
        $args = func_get_args();

        foreach ($args as $handler) {
            if (!($handler instanceof IHandler) && !is_callable($handler)) {
                throw new Exception("Invalid argument: handler", 1);
            }
            $this->handlers[] = $handler;
        }
        return $this;
    }

    /**
     * @auhtor Fabien Potencer
     * @see http://www.slideshare.net/fabpot/dependency-injection-with-php-53
     */

    protected $deps = array();

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




interface IHandler {
    /**
     * @abstract
     * @param Request $request
     * @param \Closure $container
     * @return void|bool
     */
	public function accept(Request $request, Closure $container);

    /**
     * @abstract
     * @param Request $request
     * @param Response $response
     * @param \Closure $container
     * @return void|bool
     */
	public function handle(Request $request, Response $response, Closure $container);
}


/**
 * @author Valentin Starck
 */
class Request {
    private $requestVars = array();
    private $postVars;
    private $getVars;
    private $cookieVars;
    private $uri;
    private $created;

    /**
     * @var bool
     */
    private $active = true;

    private static $instance;

    public static function create($deps = array()) {
        if (self::$instance) {
            return self::$instance;
        }

        $instance = new self;

        $instance->postVars = $_POST;
        $instance->getVars = $_GET;
        $instance->cookieVars = $_COOKIE;
        $instance->uri = isset($_GET['__req']) && $_GET['__req'] ? $_GET['__req'] : '/';
        $instance->created = microtime();

        unset($_POST, $_GET, $_COOKIE, $_REQUEST, $instance->getVars['__req']);

        foreach($deps as $key => $value) {
            $instance->{$key} = $value;
        }

        return self::$instance = $instance;
    }

    private function __construct() {
    }

    private function __clone() {
    }

    public function end() {
        $this->active = false;
    }

    public function isActive() {
        return $this->active;
    }

    /**
     * @param $key
     * @param mixed|null $fallback
     * @return mixed|null
     */
    public function post($key, $fallback = null) {
        return isset($this->postVars[$key]) ? $this->postVars[$key] : $fallback;
    }

    /**
     * @param $key
     * @param mixed|null $fallback
     * @return mixed|null
     */
    public function get($key, $fallback = null) {
        return isset($this->getVars[$key]) ? $this->getVars[$key] : $fallback;
    }

    /**
     * @param $key
     * @param mixed|null $fallback
     * @return mixed|null
     */
    public function cookie($key, $fallback = null) {
        return isset($this->cookieVars[$key]) ? $this->cookieVars[$key] : $fallback;
    }

    /**
     * @param int $index
     * @return string|null
     */
    public function segment($index = 0) {
        $parts = explode('/', $this->uri);

        return isset($parts[$index]) ? $parts[$index] : null;
    }

    /**
     * @return string|null
     */
    public function lastSegment() {
        $parts = explode('/', $this->uri);

        return end($parts);
    }

    /**
     * @return array
     */
    public function uriTail() {
        $parts = explode('/', $this->uri);

        array_shift($parts);

        return $parts;
    }

    /**
     * @return bool
     */
    public function any() {
        $args = func_get_args();

        if (!count($args)) {
            return false;
        }

        foreach ($args as $match) {
            if (strpos($this->uri, $match) !== false) {
                return true;
            }
        }

        return false;
    }

    public function uriContains($re) {
        return preg_match($re, $this->uri) > 0;
    }

    /**
     * Time elapsed
     *
     * @return int
     */
    public function elapsed() {
        return number_format(microtime() - $this->created, 3);
    }

    /**
     * Set/Get from request storage
     *
     * @param $key
     * @param null $value
     * @return null
     */
    public function value($key, $value = null) {
        if (is_null($value)) {
            return isset($this->requestVars[$key]) ? $this->requestVars[$key] : null;
        }

        $this->requestVars[$key] = $value;

        return $value;
    }

    public function path() {
        return preg_replace('/\?.+/', '', $this->uri);
    }

    public function isPost() {
        return count($this->postVars) > 0;
    }

    public function send($data) {
        echo $data;
    }
}



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



class Config {
    public function set($config) {
        $this->config = $config;
    }

    public function get($key, $fallback = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $fallback ;
    }
}



 
class FileCache {
	private static $instance;

	/**
	 *
	 * @return asadoo\core\Response
	 */
	public static function getInstance() {
		if(self::$instance) {
			return self::$instance;
		}

		$instance = new self;

		return self::$instance = $instance;
	}

    public function set($key, $value = null) {
    }

    public function get($key, $value) {

    }

    public function remove($key) {
        $this->set($key);
    }
}




class Logger {

}





abstract class AbstractFileHandler implements IHandler {
	protected $path = null;
		
	public function __construct($path = null) {		
		$this->path = $path;
	}
	
	protected function getFilePath($file) {
		return $this->path . DIRECTORY_SEPARATOR . $file;
	}
	
	public function handle(Request $request, Response $response, Closure $container) {
		if($request->lastSegment() == 'box') {
			// Concatenados		
			$content = $this->getMultipleFileContent($request->get('files', ''));
		} else {
			// Simples
			$content = $this->getFileContent($request->uriTail());
		}

		if($content === false) {			
			$response->show404();			
		}

		$response->setCacheControl(CACHE_CONTROL_FOREVER);
		$response->setMimeType($this->getMimeType());
		$response->setBody($content);
		$response->display();
		
		$request->end();
	}

	protected function getFileContent($file) {
		$file = join(DIRECTORY_SEPARATOR, $file);
		$path = $this->getFilePath($file);

		if(!file_exists($path)) {			
			return false;
		}

		//
		return file_get_contents($path);			
	}

	protected function getMultipleFileContent($files) {
		if(!$files) {
			return false;
		}

		return '/*BOX*/';
	}
	
	protected function getMimeType() {
		return 'text/plain';
	}
}




class GenericCSSHandler extends AbstractFileHandler implements IHandler {
	public function __construct($path = null) {
		parent::__construct($path);
	}
	
	public function accept(Request $request, Closure $container) {
		if($request->segment(0) == CSS_URI_SEGMENT) {
			return true;			
		}
		return false;
	}
	
	protected function getMimeType() {
		return 'text/css';
	}
}




class GenericJSHandler extends AbstractFileHandler implements IHandler {
	public function __construct($path = null) {
		parent::__construct($path);
	}
	
	public function accept(Request $request, Closure $container) {
		if($request->segment(0) == JS_URI_SEGMENT) {
			return true;			
		}
		return false;
	}
	
	protected function getMimeType() {
		return 'text/javascript';
	}
}




/**
 * new GenericPostHandler('/user/save', function($request, $response) {
 *      // stuff
 * })
 */
class GenericPostHandler implements IHandler {
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


/*
 * asadoo
 *
 * Copyright (c) 2011 Valentin Starck
 *
 * May be freely distributed under the MIT license. See the MIT-LICENSE file.
 */

define('BASE_URL', dirname($_SERVER["SCRIPT_NAME"]));
define('BASE_PATH', realpath(__DIR__));

require_once(BASE_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Asadoo.php');

/**
 * Autoloader
 *
 * @param string $className
 * @return bool
 */
spl_autoload_register(function ($className) {
        $paths = array(
            BASE_PATH . DIRECTORY_SEPARATOR . 'config',
            BASE_PATH . DIRECTORY_SEPARATOR . 'core',
            BASE_PATH . DIRECTORY_SEPARATOR . 'handlers',
            BASE_PATH . DIRECTORY_SEPARATOR . 'dependences',
        );

        $asadooName = preg_replace('/(\\\)?asadoo\\\/', '', $className);

        foreach ($paths as $path) {
            $file = $path . DIRECTORY_SEPARATOR . $asadooName . '.php';

            if (file_exists($file) && !is_dir($file)) {
                require_once($file);
                return true;
            }
        }

        // Project files
        foreach (\asadoo\Asadoo::getInstance()->config->get('project_autoload_paths', array()) as $path) {
            $file = PROJECT_PATH . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $className . '.php';

            if (file_exists($file) && !is_dir($file)) {
                require_once($file);
                return true;
            }
        }
        

        return false;
    });

set_error_handler(
    function($code, $message, $file, $line) {
        if (error_reporting() == 0) {
            return;
        }
        throw new ErrorException($message, 0, $code, $file, $line);
    }
);

set_exception_handler(
    function(ErrorException $exception) {
        echo "<pre>Uncaught exception: ", $exception->getMessage(), "\n";
        echo 'File: ' . $exception->getFile() . "\n";
        echo 'Line: ' . $exception->getLine() . "\n";
    }
);

// TODO merge project and asadoo configs
\asadoo\Asadoo::load('config/config.php');
\asadoo\Asadoo::load('config/constants.php');

