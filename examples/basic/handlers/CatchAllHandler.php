<?php
class CatchAllHandler implements asadoo\IHandler {
	public function accept(asadoo\Request $request, Closure $container) {
		return true;
	}

	public function handle(asadoo\Request $request, asadoo\Response $response, Closure $container) {
		$response->setView(PROJECT_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'body.php');

		$response->value('title', 'Asadoo');
		$response->value('elapsed', $request->elapsed());
		$response->value('name', $request->get('name', 'world'));

		$response->display();

        return false;
	}
}