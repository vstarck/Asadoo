<?php
class CatchAllHandler implements asadoo\core\IHandler {
	public function accept(asadoo\core\Request $request, Closure $container) {
		return true;
	}

	public function handle(asadoo\core\Request $request, asadoo\core\Response $response, Closure $container) {
		$response->setView(PROJECT_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'body.php');

		$response->value('title', 'Asadoo');
		$response->value('elapsed', $request->elapsed());
		$response->value('name', $request->get('name', 'world'));

		$response->display();

        return false;
	}
}