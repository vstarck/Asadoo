<?php
namespace asadoo;
 
class FileCache {
	private static $instance;

    private $path;

	/**
	 *
	 * @return asadoo\core\Response
	 */
	public static function getInstance() {
		if(self::$instance) {
			return self::$instance;
		}

		$instance = new self;

        $instance->path = PROJECT_PATH . DIRECTORY_SEPARATOR . 'cache';

		return self::$instance = $instance;
	}

    public function set($key, $value) {
        if(!file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $filePath = $this->getFilePath($key);

        file_put_contents($filePath, $value);
        return $value;
    }

    public function get($key) {
        $filename = $this->getFilePath($key);

        if(!file_exists($filename)) {
            return null;
        }

        return file_get_contents($filename);
    }

    public function remove($key) {
        $this->set($key);
    }

    private function getFilePath($key) {
        return $this->path . DIRECTORY_SEPARATOR . md5($key);
    }

    public function setPath($path) {
        $this->path = $path;
    }
}
