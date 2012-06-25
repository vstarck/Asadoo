<?php
include '../../dist/asadoo.php';

/*
 * Matches
 *
 *  /           -> "Hello world"
 *  /foo        -> "Hello foo"
 *  /foo/       -> "Hello foo"
 *  /foo/bar    -> "Hello foo"
 */

asadoo()
    ->on('/')
    ->on('/:name')
    ->on('/:name/*')
    ->handle(function($memo, $name = 'world') {
        $this->res->write('Hello ', $name);
    });

asadoo()->start();