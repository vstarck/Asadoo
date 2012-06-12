<?php

require 'builder.class.php';

$builder = new Builder();

$builder->
    // Config
    set_dest('../dist/asadoo.php')->
    set_path('../src/')->

    format(function($fileContent, $filename) {
        if(strpos($filename, 'header.php') !== false) {
            return $fileContent;
        }

        $fileContent = str_replace('<?php', '', $fileContent);
        $fileContent = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $fileContent);
        $fileContent = preg_replace("/\/\*[^\/]*/", '', $fileContent);
        return $fileContent;
    })->

    // Files
    add_file('header.php')->
    add_file('AsadooMixin.php')->
    add_file('AsadooCore.php')->    
    add_file('pimple_header.php')->
    add_file('Pimple.php')->
    add_file('AsadooRequest.php')->
    add_file('AsadooResponse.php')->
    add_file('AsadooHandler.php')->
    add_file('AsadooFacade.php');


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


