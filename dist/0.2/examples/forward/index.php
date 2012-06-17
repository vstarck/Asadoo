<?php

include '../../dist/asadoo.php';

asadoo()
    ->name('handler-1')
    ->handle(function($request, $response, $dependences) {
        $response->write('handler-1</br>');
    });

asadoo()
    ->name('handler-2')
    ->handle(function($request, $response, $dependences) {
        $response->write('handler-2</br>');
    });

asadoo()
    ->name('handler-3')
    ->handle(function($request, $response, $dependences) {
        $response->write('handler-3</br>');
    });

asadoo()
    ->on('*')
    ->handle(function($request, $response, $dependences) {
        $request->forward('handler-' . rand(1, 3));
    });

asadoo()->start();