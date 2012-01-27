<?php

include '../../dist/asadoo.php';

// Fake dependences
asadoo()->dependences()->register('config', (object) array(
    'version' => '0.2'
));

class View {
    public function load() {
        return 'Fake load view!<br/><br/>';
    }
}

asadoo()->dependences()->register('view', function() {
    return new View();
});

// Using dependences
asadoo()
    // All requests
    ->on('*')
    ->handle(function($request, $response, $dependences) {
        $view = $dependences->view;

        $response->write(
            $view->load('/my/crazy/view.php')
        );
    });

// Multiples rules
asadoo()
    // Capture by string rule
    ->on('/view/:id')
    ->on('/view')
    // Functional capture, matchs /foo/bar/view/baz
    ->on(function($request, $response, $dependences) {
        return $request->has('view');
    })
    ->handle(function($request, $response, $dependences) {
        // Captured argument
        $id = $request->get('id', 'ID not found!');

        // Response body
        $response->write($id);

        // No other handler will be invoked
        $response->end();
    });

// Server error
asadoo()
    ->on('/error')
    ->handle(function($request, $response, $dependences) {
        // Server error!
        $response->code(500);

        $response->write('Error!');

        // No other handler will be invoked
        $response->end();
    });

// Segments
asadoo()
    ->on('/segment/*')
    ->handle(function($request, $response, $dependences) {
        $index = 0;
        while($segment = $request->segment($index++)) {
            $response->write('Segment ' . $index . ': ' . $segment . '<br/>');
        }

        // No other handler will be invoked
        $response->end();
    });


// 404 for everyone!
asadoo()
    // All requests
    ->on('*')
    ->handle(function($request, $response, $dependences) {
        $response->code(404);

        $response->write('404');
        $response->write(
            '<br/>Asadoo ' .
            $dependences->config->version
        );
        $response->end();
    });

// Aaaaand, go!
asadoo()->start();