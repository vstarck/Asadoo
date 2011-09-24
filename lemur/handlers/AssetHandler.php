<?php
namespace lemur\handlers;
use \lemur\core;

class AssetHandler implements \lemur\core\IHandler {
	public function accept(core\Request $request) {
		if($request->segment(0) == ASSET_URI_SEGMENT) {
			return true;			
		}
		return false;
	}

	public function handle(core\Request $request, core\Response $response) {
		return false;
	}
}
