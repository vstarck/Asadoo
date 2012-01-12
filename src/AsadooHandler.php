<?php
class AsadooHandler {
	public $conditions = array();
	public $fn;
	public $finisher = false;

	public function on($condition) {
		$this->conditions[] = $condition;
		return $this;
	}

	public function handle($fn) {
		$this->fn = $fn;
		$this->register($this);

		return $this;
	}

	public function close() {
		$this->finisher = true;
		return $this;
	}

	public function dependences() {
		return AsadooCore::getInstance()->dependences;
	}

	public function start() {
		AsadooCore::getInstance()->start();
		return $this;
	}

	private function register($handler) {
		AsadooCore::getInstance()->add($handler);
	}
}
