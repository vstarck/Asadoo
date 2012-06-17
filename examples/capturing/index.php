<?php
include '../../dist/asadoo.php';

asadoo()
    ->on('/')
    ->on('/:name')
    ->handle(function($memo, $req, $res, $dependences) {
        $res->write('Hello ', $req->value('name', 'world'), '!');
    });

asadoo()->start();