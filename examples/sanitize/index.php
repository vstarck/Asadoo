<?php

include '../../dist/asadoo.php';

asadoo()->setSanitizer(function($value, $type, $dependences) {
    return preg_replace('/[^a-z\d]/', '', $value);
});

asadoo()
    ->on('/inject/get/')
    ->handle(function($request, $response, $dependences) {

        $response->send(
            $request->get('value')
        );

        $response->end();
    });

// Index
asadoo()
    // All requests
    ->on('*')
    ->handle(function($request, $response, $dependences) {
        $base = $request->getBaseURL();

        $response->send(
            '<html><head><title>Sanitize</title></head><body><ul>',
            "<li><a href=\"{$base}/inject/get?value='1=1\">inject/get/?s='1</a></li>"
        );
    });

// Aaaaand, go!
asadoo()->start();