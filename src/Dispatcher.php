<?php
namespace asadoo;

final class Dispatcher extends Mixin {
    /**
     * @var Core
     */
    private $core;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var \Pimple
     */
    private $dependences;

    /**
     * @param Core $core
     */
    public function __construct($core) {
        $this->core = $core;
        $this->request = $core->request;
        $this->response = $core->response;
        $this->dependences = $core->dependences;
    }

    /**
     * @param array $conditions
     * @return bool
     */
    private function match($conditions) {
        foreach ($conditions as $condition) {
            if ($this->matchCondition($condition)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $condition
     * @return bool
     */
    private function matchCondition($condition) {
        if (is_callable($condition) && $condition($this->request, $this->response, $this->dependences)) {
            return true;
        }

        if (!is_string($condition)) {
            if (trim($condition) == '*') {
                return true;
            }
            if (trim($condition) == '/') {
                return str_replace('/', '', $this->request->path()) === '';
            }
            if ($this->matchStringCondition($condition)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $condition
     * @return string
     */
    private function formatStringCondition($condition) {
        $condition = str_replace('*', '.*?', $condition);

        if (substr($condition, -1, 1) == '/') {
            $condition = substr($condition, 0, -1) . '/?';
        }

        $condition = preg_replace('/\//', '\/', $condition) . '$';

        return $condition;
    }

    // TODO refactor
    private function matchStringCondition($condition) {
        $url = $this->request->path();

        $keys = array();

        $condition = '/^' . $this->formatStringCondition($condition) . '/';

        while (strpos($condition, ':') !== false) {
            $matches = array();

            if (preg_match('/:(\w+)/', $condition, $matches)) {
                $keys[] = $matches[1];

                $condition = preg_replace('/:\w+/', '([^\/\?\#]+)', $condition, 1);
            }
        }

        $values = array();

        $result = preg_match($condition, $url, $values);

        if (!$result) {
            return false;
        }

        if (count($keys)) {
            array_shift($values);

            $this->request->set(
                array_combine($keys, $values)
            );
        }

        return true;
    }
}
