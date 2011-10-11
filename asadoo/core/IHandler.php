<?php
namespace asadoo\core;

interface IHandler {
    /**
     * @abstract
     * @param Request $request
     * @return bool
     */
	public function accept(Request $request);

    /**
     * @abstract
     * @param Request $request
     * @param Response $response
     * @return void|bool
     */
	public function handle(Request $request, Response $response);	
}