<?php
include '../../dist/asadoo.php';

asadoo()
    ->on('/')
    ->on('/:foo')
    ->on('/:foo/:bar')
    ->on('/:foo/:bar/:baz')
    ->on('/:foo/:bar/:baz/*')
    ->handle(function($memo, $baz = 'baz', $foo = 'foo', $bar = 'bar') {
        $this->res->write(
            'foo: ' . $foo,
            '<br />',
            'bar: ' . $bar,
            '<br />',
            'baz: ' . $baz
        );
    });

asadoo()->start();