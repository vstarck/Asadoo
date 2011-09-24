<?php
namespace lemur\handlers;
use \lemur\core;

/**
 * TODO pasar la logica de servicio de archivos estaticos a una clase abstracta
 *
 */
class JSHandler implements \lemur\core\IHandler {
	public function accept(core\Request $request) {
		if($request->segment(0) == JS_URI_SEGMENT) {
			return true;			
		}
		return false;
	}

	public function handle(core\Request $request, core\Response $response) {

		if($request->lastSegment() == 'box') {
			// Concatenados		
			$content = $this->getMultipleScriptsContent($request->get('files', ''));
		} else {
			// Simples
			$content = $this->getScriptContent($request->uriTail());
		}

		if(!$content) {			
			$response->show404();
		}

		$response->setCacheControl(CACHE_CONTROL_FOREVER);
		$response->setBody($content);
	}

	private function getScriptContent($file) {
		$file = join(DIRECTORY_SEPARATOR, $file);
		$path = $this->getScriptPath($file);

		if(!file_exists($path)) {			
			return false;
		}

		//
		return file_get_contents($path);			
	}

	// TODO path manager?
	private function getScriptPath($file) {
		return BASE_PATH . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $file;
	}

	private function getMultipleScriptsContent($files) {
		if(!$files) {
			return false;
		}

		return '/*BOX*/';
	}
}