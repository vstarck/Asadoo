<?php
namespace asadoo\core;

interface IHandler {
	public function accept(Request $request);

	public function handle(Request $request, Response $response);	
}