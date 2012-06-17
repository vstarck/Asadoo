<?php

include '../../dist/asadoo.php';

asadoo()->before(function($request, $response, $dependences) {
    $request->set('who', 'world');
});
asadoo()->after(function($request, $response, $dependences) {
    $response->format(function($output) {
        return strtoupper($output);
    });
});
asadoo()
    ->on('*')
    ->handle(function($request, $response, $dependences) {
        $response->write(
            'hello ' . $request->value('who', 'John Doe')
        );
    });


asadoo()->start();