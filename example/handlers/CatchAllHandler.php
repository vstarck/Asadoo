<?php
class CatchAllHandler implements lemur\core\IHandler {
	public function accept(lemur\core\Request $request) {
		return true;
	}

	public function handle(lemur\core\Request $request, lemur\core\Response $response) {
		$response->setView('body.view');

		$response->set('title', 'Test');
		$response->set('name', $request->get('name', 'world'));
	}
}