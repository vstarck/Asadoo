<?php
namespace asadoo;
use Closure;

interface IHandler {
    /**
     * @abstract
     * @param Request $request
     * @param \Closure $container
     * @return void|bool
     */
	public function accept(Request $request, Closure $container);

    /**
     * @abstract
     * @param Request $request
     * @param Response $response
     * @param \Closure $container
     * @return void|bool
     */
	public function handle(Request $request, Response $response, Closure $container);
}