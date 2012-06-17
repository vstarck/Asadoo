<?php
include '../../dist/asadoo.php';

error_reporting(E_ALL);

asadoo()
    ->on('/')
    ->on('/:name')
    ->handle(function($memo, $req, $res, $dependences) {
        $res->write('Hello ', $req->value('name', 'world'), '!');
    });

asadoo()->start();