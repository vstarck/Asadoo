<?php
namespace lemur\handlers;
use \lemur\core;

class GenericCSSHandler extends AbstractFileHandler implements \lemur\core\IHandler {
	public function __construct($path = null) {
		parent::__construct($path);
	}
	
	public function accept(core\Request $request) {
		if($request->segment(0) == CSS_URI_SEGMENT) {
			return true;			
		}
		return false;
	}
}