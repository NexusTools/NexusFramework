<?php
class Registry extends Lockable {

	private $path;
	private $values = Array();
	private static $basepath = false;
	private static $registryCache = Array();

	public function getInstance($path = "") {
		$path = relativepath($path);
		if (!array_key_exists($path, self::$registryCache))
			self::$registryCache[$path] = new Registry($path);

		return self::$registryCache[$path];
	}

	private function __construct($path) {
		if ($this->basepath === false)
			$this->basepath = Framework::getTempFolder("Registry");
	}

	public function keys() {
		return array_keys($this->values);
	}

	public function clear() {
	}

	public function __get($key) {
		return $this->values[$key];
	}

	public function __set($key) {
		return $this->values[$key];
	}

	public function __isset() {

	}

	public function __toString() {
		return json_encode($this->values);
	}

}
?>
