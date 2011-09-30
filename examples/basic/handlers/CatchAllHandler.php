<?php
class CatchAllHandler implements asadoo\core\IHandler {
	public function accept(asadoo\core\Request $request) {
		return true;
	}

	public function handle(asadoo\core\Request $request, asadoo\core\Response $response) {
		$response->setView(PROJECT_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'body.php');

		$response->set('title', 'Asadoo');
		$response->set('elapsed', $request->elapsed());
		$response->set('name', $request->get('name', 'world'));
	}
}