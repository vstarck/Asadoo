<?php
namespace lemur\handlers;
use \lemur\core;

class BackendHandler implements core\IHandler {
	public function accept(core\Request $request) {
		if($request->segment(0) == 'name') {
			return true;
		}

		return false;
	}

	public function handle(core\Request $request, core\Response $response) {
		$response->setView('body.view');

		$response->set('title', 'Test');
		$response->set('name', $request->get('name', 'world'));
	}
}