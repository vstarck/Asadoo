<?php
include '../../dist/asadoo.php';
include 'data.php';

// Basically, all services
asadoo()
    ->on('/:entity')
    ->on('/:entity/:id')
    ->handle(function($memo, $entityName) {
        $entity = MyFakeEntityFactory::create($entityName);

        if(!$entity) {
            return $this->forward('error', 400);  //Bad Request
        }

        return $entity;
    });

// RESTful interface
asadoo()
    ->on('/:entity')
    ->delete(function($memo) {
        return $memo->delete();
    })
    ->get(function($memo) {
        return array(
            'items' => $memo->get(),
            'count' => count($memo->get())
        );
    })
    ->post(function($memo) {
        return $memo->create(array(
            'name' => $this->req->post('name'),
            'lastname' => $this->req->post('lastname')
        ));
    })
    ->put(function($memo) {
        $this->forward('error', 405);  // Method not allowed
    });


asadoo()
    ->on('/:entity/:id')
    ->delete(function($memo, $id) {
        return $memo->delete($id);
    })
    ->get(function($memo, $id) {
        return $memo->get($id);
    })
    ->post(function($memo) {
        $this->forward('error', 405);  // Method not allowed
    })
    ->put(function($memo, $id) {
        return $memo->put($id, array(
            'name' => $this->req->post('name'),
            'lastname' => $this->req->post('lastname')
        ));
    });

// Our error handler
asadoo()
    ->name('error')
    ->handle(function($memo) {
        $this->response
                ->code($memo)
                ->end();
    });


// JSON output (for all services)
asadoo()
    ->on('/:entity')
    ->on('/:entity/:id')
    ->handle(function($memo, $entity) {
        $result = json_encode(array(
            'status' => !empty($memo) ? true : false,
            'entity' => $entity,
            'method' => $this->req->method(),
            'data' => $memo,
            'time' => time()
        ));

        $this->response->end($result);
    });

// All unattended requests
asadoo()->on('*')->handle(function($memo) {
    $this->response->code(404)->end('404');
});

asadoo()->start();