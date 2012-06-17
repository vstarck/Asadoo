<?php
require 'build.php';
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
        <hr/>
        <pre>
<?php echo htmlentities($builder->text()); ?>
        </pre>

    </body>
</html>