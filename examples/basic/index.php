<?php
include '../../dist/asadoo.php';

asadoo()
    ->on('*')
    ->handle(function($memo, $req, $res, $dependences) {
        $res->write('Hello world!');
    });

asadoo()->start();