<?php
namespace asadoo\dependences;
 
class FileCache {
	private static $instance;

	/**
	 *
	 * @return asadoo\core\Response
	 */
	public static function getInstance() {
		if(self::$instance) {
			return self::$instance;
		}

		$instance = new self;

		return self::$instance = $instance;
	}

    public function set($key, $value = null) {
    }

    public function get($key, $value) {

    }

    public function remove($key) {
        $this->set($key);
    }
}
