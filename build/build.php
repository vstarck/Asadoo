<?php

require 'Builder.php';

function stripComments($text) {
    $text = preg_replace('!/\*.*?\*/!s', '', $text);
    $text = preg_replace('/\n\s*\n/', "\n", $text);

    return $text;
}

$builder = new Builder();

$builder->
    // Config
    set_dest('../dist/asadoo.php')->
    set_path('../src/class/')->

    format(function($fileContent, $filename) {
        if(strpos($filename, 'header') !== false) {
            return $fileContent;
        }

        if(strpos($filename, 'namespace') !== false) {
            return  PHP_EOL . $fileContent;
        }

        if(!preg_match('/(header|copyright)/', $filename)) {
            $fileContent = stripComments($fileContent);
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
    add_file('pimple_copyright.tpl', './')->
    add_file('Pimple.php', '../src/vendor/')->
    add_file('open_namespace.tpl', './')->
    add_file('Mixable.php')->
    add_file('Core.php')->
    add_file('Request.php')->
    add_file('Response.php')->
    add_file('Matcher.php')->
    add_file('Handler.php')->
    add_file('Facade.php')->
    add_file('ExecutionContext.php')->
    add_file('close_namespace.tpl', './')->
    add_file('asadoo.fn.php', '../src/');


// Enjoy!
$builder->process();


