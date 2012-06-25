<?php
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
    public function get($id = null) {
        if(!is_null($id)) {
            return isset($this->data[$id]) ? $this->data[$id] : null ;
        }
        return (object) $this->data;
    }
}

class MyFakeEntityFactory {
    public static function create($entity) {
        return new MyFakeEntity();
    }
}