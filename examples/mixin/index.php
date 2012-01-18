<?php

include '../../dist/asadoo.php';

class RequestExtended {
    public function ip($asadooRequestInstance) {
        return $_SERVER['REMOTE_ADDR'];
    }
}

AsadooRequest::mix(new RequestExtended());

asadoo()->get('*', function($request, $response, $dependences) {
    $response->send(
        // The new method
        $request->ip()
    );
});

asadoo()->start();