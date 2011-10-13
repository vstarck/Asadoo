<?php

class DocumentationHandler implements asadoo\IHandler {
	public function accept(asadoo\Request $request, Closure $container) {
        return $request->segment(1) == 'docs';
	}

	public function handle(asadoo\Request $request, asadoo\Response $response, Closure $container) {
		$response->setView(PROJECT_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'body.php');

		$response->value('title', 'Asadoo :: Documentacion [' . strtoupper($request->segment(0)) . ']');
		$response->value('elapsed', $request->elapsed());
		$response->value('name', $request->get('name', 'world'));

		$response->display();

        return false;
	}
}