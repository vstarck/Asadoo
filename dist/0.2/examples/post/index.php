<?php

include '../../dist/asadoo.php';

// GET
asadoo()->get('*', function($request, $response, $dependences) {
    $response->write(
        '<form action="" method="POST">',
        '<input type="text" name="field" value="1"/>',
        '<input type="submit" value="Submit"/>',
        '</form>'
    );
});

// POST
asadoo()->post('*', function($request, $response, $dependences) {
    $response->write(
        'field: ' . $request->post('field')
    );
});

// Aaaaand, go!
asadoo()->start();