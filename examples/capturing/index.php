<?php
include '../../dist/asadoo.php';

error_reporting(E_ALL);

asadoo()
    ->on('*')
    ->handle(function($memo, $req, $res, $dependences) {
        $res->write('Hello world!');
    });

asadoo()->start();