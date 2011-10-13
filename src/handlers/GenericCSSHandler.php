<?php
namespace asadoo;
use Closure;

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