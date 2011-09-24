<?php
namespace lemur\handlers;
use \lemur\core;

/**
 * TODO pasar la logica de servicio de archivos estaticos a una clase abstracta
 *
 */
class JSHandler extends AbstractFileHandler implements \lemur\core\IHandler {
	public function accept(core\Request $request) {
		if($request->segment(0) == JS_URI_SEGMENT) {
			return true;			
		}
		return false;
	}

	protected function getFilePath($file) {
		return PROJECT_PATH . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $file;
	}
}