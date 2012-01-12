<?php

require 'builder.class.php';

$builder = new Builder();

$builder->
    // Config
    set_separator(PHP_EOL)->
    set_dest('../dist/asadoo.php')->

    // Files
    add_directory('../src');

// Enjoy!
$builder->process();

echo $builder->result();
