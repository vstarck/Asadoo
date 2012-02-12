<?php

include '../../dist/asadoo.php';

// GET
asadoo()->get('*', function($request, $response, $dependences) {
    $request->forward('catch-me!');
});

// POST
asadoo()->name('catch-me!')->handle(function($request, $response, $dependences) {
    $response->write('Hello World!');
});

// Aaaaand, go!
asadoo()->start();