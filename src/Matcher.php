<?php
namespace asadoo;

final class Matcher extends Mixin {
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
     * @param Request $request
     * @param Response $response
     * @param \Pimple $dependences
     */
    public function __construct($core, $request, $response, $dependences) {
        $this->core = $core;
        $this->request = $request;
        $this->response = $response;
        $this->dependences = $dependences;
    }

    /**
     * @param array $conditions
     * @return bool
     */
    public function match($conditions) {
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
            if (trim($condition) === '*') {
                return true;
            }
            if (trim($condition) === '/') {
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

        if (substr($condition, -1, 1) === '/') {
            $condition = substr($condition, 0, -1) . '/?';
        }

        $condition = preg_replace('/\//', '\/', $condition) . '$';

        return $condition;
    }

    private function matchStringCondition($condition) {
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

        $result = preg_match($condition, $this->request->path(), $values);

        $this->setupValues($keys, $values);

        return $result;
    }

    private function setupValues($keys, $values) {
        if (count($keys)) {
            array_shift($values);

            $this->request->set(
                array_combine($keys, $values)
            );
        }
    }
}
