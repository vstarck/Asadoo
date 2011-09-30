<?php
namespace asadoo\handlers;
use \asadoo\core;

/**
 * TODO pasar la logica de servicio de archivos estaticos a una clase abstracta
 *
 */
abstract class AbstractFileHandler implements \asadoo\core\IHandler {
	protected $path = null;
		
	public function __construct($path = null) {		
		$this->path = $path;
	}
	
	protected function getFilePath($file) {
		return $this->path . DIRECTORY_SEPARATOR . $file;
	}
	
	public function handle(core\Request $request, core\Response $response) {
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
        
		return false;
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