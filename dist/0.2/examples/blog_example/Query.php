<?php
class Query {
    private static $data = array();

    public static function load($entity, $data) {
        if (!isset(self::$data[$entity])) {
            self::$data[$entity] = array();
        }

        self::$data[$entity] += $data;
    }

    private $entity = null;
    private $condition = array();

    public function from($entity) {
        $this->entity = $entity;
        return $this;
    }

    public function where($field, $value) {
        $this->condition = array(
            'field' => $field,
            'value' => $value
        );
        return $this;
    }

    public function get() {
        $data = self::$data;

        if (!isset($data[$this->entity])) {
            return array();
        }

        $result = array();

        foreach ($data[$this->entity] as $row) {
            $matches = true;

            if (count($this->condition) && $row[$this->condition['field']] != $this->condition['value']) {
                $matches = false;
            }

            if($matches) {
                $result[] = $row;
            }
        }

        return $result;
    }

    public function count() {
        return count($this->get());
    }
}


Query::load('post', array(
    array(
        'id' => 1,
        'title' => 'Presentación',
        'body' => 'Esta es la presentación de la primer entrada del ejemplo',
        'date' => '10/01/2012',
        'author' => 'Valentín Starck',
        'url' => '/2012/01/10/presentacion'
    ),
    array(
        'id' => 2,
        'title' => 'Continuación',
        'body' => 'Esta es la presentación de la segunda entrada del ejemplo',
        'date' => '13/01/2012',
        'author' => 'Valentín Starck',
        'url' => '/2012/01/13/continuacion'
    ),
    array(
        'id' => 3,
        'title' => 'Finalización',
        'body' => 'Esta es la presentación de la tercer entrada del ejemplo',
        'date' => '16/01/2012',
        'author' => 'Valentín Starck',
        'url' => '/2012/01/16/finalizacion'
    ),
));