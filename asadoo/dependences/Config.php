<?php
namespace asadoo\dependences;

class Config {
    public function set($config) {
        $this->config = $config;
    }

    public function get($key, $fallback = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $fallback ;
    }
}
