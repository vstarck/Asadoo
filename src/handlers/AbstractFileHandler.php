<?php
namespace asadoo;
use Closure;

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