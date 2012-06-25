<?php
namespace {
    function asadoo($instance = null) {
        return new \asadoo\Facade(
            is_null($instance) ? \asadoo\Core::getInstance(): $instance
        );
    }
}