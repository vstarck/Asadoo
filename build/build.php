<?php

require 'builder.class.php';

$builder = new Builder();

$builder->
    // Config
    set_separator(PHP_EOL)->
    set_dest('../dist/asadoo.php')->
    set_path('../src/')->

    format(function($fileContent, $filename) {
        if(strpos($filename, 'header.php') !== false) {
            return $fileContent;
        }

        $fileContent = '// From file: ' . $filename . PHP_EOL . $fileContent;

        return str_replace('<?php', '', $fileContent);
    })->

    // Files
    add_file('header.php')->
    add_file('AsadooCore.php')->
    add_file('AsadooDependences.php')->
    add_file('AsadooRequest.php')->
    add_file('AsadooResponse.php')->
    add_file('AsadooHandler.php');


// Enjoy!
$builder->process();

?>
<html>
    <head>
        <title>Builder</title>
    </head>
    <script>
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    </script>
    <body>
        <h1>Proccesed: <?php echo $builder->result() ?></h1>
    </body>
</html>


