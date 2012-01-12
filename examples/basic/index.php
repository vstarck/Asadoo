<?php

include '../../dist/asadoo.php';

asadoo()->dependences()->register('config', (object) array(
    'version' => '0.2'
));

asadoo()
    ->on('/view/:id')
    ->on('/view')
    ->handle(function($request, $response, $dependences) {
        $id = $request->get('id', 'ID not found!');

        $response->setResponseCode(203);
        $response->send($id);
        $response->end();
    });


asadoo()
    ->on('*')
    ->handle(function($request, $response, $dependences) {
        $response->setResponseCode(404);
/*
        $response->send('404');
        $response->send(
            '<br/>Asadoo ' .
            $dependences->config->version
        );*/
        $response->end();
    });

asadoo()->start();