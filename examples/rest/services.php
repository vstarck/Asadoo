<?php
include '../../dist/asadoo.php';
include 'data.php';

// Basically, all services
asadoo()
    ->on('/:entity')
    ->on('/:entity/:id')
    ->handle(function($memo, $req, $res, $dependences) {
        $entity = MyFakeEntityFactory::create($req->value('entity'));

        if(!$entity) {
            $res
                ->code(400) // Bad Request
                ->end();
        }

        return $entity;
    });

// RESTful interface
asadoo()
    ->on('/:entity')
    ->delete(function($memo, $req, $res, $dependences) {
        return $memo->delete();
    })
    ->get(function($memo, $req, $res, $dependences) {
        return $memo->get();
    })
    ->post(function($memo, $req, $res, $dependences) {
        return $memo->create(array(
            'name' => $req->post('name'),
            'lastname' => $req->post('lastname')
        ));
    })
    ->put(function($memo, $req, $res, $dependences) {
        $res
            ->code(405) // Method not allowed
            ->end();
    });


asadoo()
    ->on('/:entity/:id')
    ->delete(function($memo, $req, $res, $dependences) {
        return $memo->delete($req->value('id'));
    })
    ->get(function($memo, $req, $res, $dependences) {
        return $memo->get($req->value('id'));
    })
    ->post(function($memo, $req, $res, $dependences) {
        $res
            ->code(405) // Method not allowed
            ->end();
    })
    ->put(function($memo, $req, $res, $dependences) {
        return $memo->put($req->value('id'), array(
            'name' => $req->post('name'),
            'lastname' => $req->post('lastname')
        ));
    });

// JSON output (for all services)
asadoo()
    ->on('/:entity')
    ->on('/:entity/:id')
    ->handle(function($memo, $req, $res, $dependences) {
        $result = json_encode(array(
            'status' => !empty($memo) ? true : false,
            'entity' => $req->value('entity'),
            'method' => $req->method(),
            'data' => $memo,
            'time' => time()
        ));

        $res->end($result);
    });

asadoo()->on('*')->handle(function($memo, $req, $res) {
    $res->code(404)->end('404');
});

asadoo()->start();