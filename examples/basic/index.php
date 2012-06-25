<?php
include '../../dist/asadoo.php';

/*
 * Matches
 *
 *  /           -> "Hello world!"
 *  /foo        -> "Hello world!"
 *  /foo/       -> "Hello world!"
 *  /foo/bar    -> "Hello world!"
 */

asadoo()
    ->on('*')
    ->handle(function($memo) {
       $this->res->write('Hello world!');
    });

asadoo()->start();