<?php

require 'Builder.php';

$builder = new Builder();

$builder->
    // Config
    set_dest('../dist/asadoo.php')->
    set_path('../src/')->

    format(function($fileContent, $filename) {
        if(strpos($filename, 'header') !== false) {
            return $fileContent;
        }

        //$fileContent = preg_replace("/\/\*[^\/]*/", '', $fileContent);
        $fileContent = str_replace('<?php', '', $fileContent);
        $fileContent = preg_replace("/namespace\s\w[^\n\r]*/", '', $fileContent);
        $fileContent = preg_replace("/\s*$/", '', $fileContent);
        $fileContent = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $fileContent);

        return $fileContent;
    })->

    // Files
    add_file('header.tpl', './')->
    add_file('Mixin.php')->
    add_file('Core.php')->
    add_file('Request.php')->
    add_file('Response.php')->
    add_file('Matcher.php')->
    add_file('Handler.php')->
    add_file('Facade.php')->
    add_file('footer.tpl', './')->
    add_file('Pimple.php')->
    add_file('asadoo.fn.php');


// Enjoy!
$builder->process();


