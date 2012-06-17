<?php
include '../../dist/asadoo.php';

class MyFakeEntity {
    private $data = array(
        0 => array(
            'name' => 'John',
            'lastname' => 'Doe'
        ),
        1 => array(
            'name' => 'Jeff',
            'lastname' => 'Perkins'
        ),
        2 => array(
            'name' => 'Dustin',
            'lastname' => 'Lock'
        ),
        3 => array(
            'name' => 'Edward',
            'lastname' => 'Johnson'
        )
    );

    public function update($id, $data) {}
    public function create($data) {}
    public function delete($id = null) {}
    public function get($ids = null) {}
}

class MyFakeEntityFactory {
    public static function create($entity) {
        return new MyFakeEntity();
    }
}

asadoo()
    ->on('/:entity')
    ->on('/:entity/:id')
    ->handle(function($memo, $req, $res, $dependences) {
        return MyFakeEntityFactory::create($req->value('entity'));
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
            // ... our values
        ));
    });

asadoo()
    ->on('/:entity/:id')
    ->delete(function($memo, $req, $res, $dependences) {
        return $memo->delete($req->value('id'));
    })
    ->get(function($memo, $req, $res, $dependences) {
        return $memo->get($req->value('id'));
    })
    ->put(function($memo, $req, $res, $dependences) {
        return $memo->put($req->value('id'), array(
            // ... our values
        ));
    });

// to JSON
asadoo()
    ->on('/:entity')
    ->on('/:entity/:id')
    ->handle(function($memo, $req, $res, $dependences) {
        return json_encode(array(
            'status' => $memo ? true : false,
            'entity' => $req->value('entity'),
            'data' => $memo,
            'time' => time()
        ));
    });

asadoo()->start();