<?php

include '../../dist/asadoo.php';

asadoo()
    ->on('/view/:id')
    ->on('/view')
    ->handle(function($request, $response, $dependences) {
        $id = $request->get('id', 'ID not found!');

        $response->send($id);
    })
    ->close();


asadoo()
    ->on('*')
    ->handle(function($request, $response, $dependences) {
        $response->header404();
        $response->send('404');
    })
    ->close();

asadoo()->start();