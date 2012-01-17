<?php

include '../../dist/asadoo.php';

class RequestExtended {
    public function ip($asadooRequestInstance) {
        return $_SERVER['REMOTE_ADDR'];
    }
}

AsadooRequest::mix(new RequestExtended());

// GET
asadoo()->get('*', function($request, $response, $dependences) {
    $response->send(
        $request->ip()
    );
});

// Aaaaand, go!
asadoo()->start();