<?php
namespace asadoo;

final class Matcher {
    use Mixable;
    /**
     * @var Core
     */
    private $core;

    /**
     * @var \asadoo\ExecutionContext
     */
    private $executionContext;

    /**
     * @var \asadoo\Request
     */
    private $request;

    /**
     * @param Core $core
     * @param Request $request
     * @param Response $response
     * @param \Pimple $dependences
     */
    public function __construct($core, $executionContext) {
        $this->core = $core;
        $this->executionContext = $executionContext;
        $this->request = $executionContext->request;
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
    public function matchCondition($condition) {
        if (is_callable($condition)) {
            return $this->core->exec($condition);
        }

        if (is_string($condition)) {
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
        } else {
            $condition .= '/?';
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

        if ($result) {
            $this->setupValues($keys, $values);
        }

        return $result;
    }

    private function setupValues($keys, $values) {
        array_shift($values);

        if (count($keys) && count($values) === count($keys)) {
            $this->request->set(array_combine($keys, $values));
        }
    }
}
