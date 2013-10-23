<?php
class Settings {

	private $path;
	private $data = Array();
	private $marked = false;

	public function __construct($extension, $name = "settings") {
		$this->path = Framework::getConfigFolder($extension)."$name.json";

		if (file_exists($this->path))
			$this->data = json_decode(file_get_contents($this->path), true);
		else
			$this->saveLater();

		if (!is_array($this->data)) {
			$this->data = Array();
			$this->saveLater();
		}
	}

	protected function saveLater() {
		if (!$this->marked) {
			$this->marked = true;
			register_shutdown_function(Array($this, "save"));
		}
	}

	public function getValue($key, $def = null) {
		if (array_key_exists($key, $this->data))
			return $this->data[$key];
		else
			return $this->setValue($key, $def);
	}

	public function hasValue($key, $def = null) {
		return array_key_exists($key, $this->data);
	}

	public function setValue($key, $val) {
		if ($val === null)
			$this->unsetValue($key);
		else
			if ($this->data[$key] !== $val) {
				$this->data[$key] = $val;
				$this->saveLater();
			}

		return $val;
	}

	public function unsetValue($key) {
		if ($this->hasValue($key)) {
			unset($this->data[$key]);
			$this->saveLater();
		}
	}

	public function __set($key, $val) {
		$this->setValue($key, $val);
	}

	public function __get($key) {
		return $this->getValue($key);
	}

	public function __isset($key) {
		return $this->hasValue($key);
	}

	public function __unset($key) {
		$this->unsetValue($key);
	}

	public function getString($key, $def = null) {
		if ($this->isValidString($key))
			return (string) $this->getValue($key);
		else
			return $this->setValue($key, is_string($def) && strlen($def) ? $def : null);
	}

	public function getNumber($key, $def = null) {
		if ($this->isValidNumber($key))
			return (float) $this->getValue($key);
		else
			return $this->setValue($key, is_numeric($def) ? $def : null);
	}

	public function getBoolean($key, $def = null) {
		if ($this->isValidBoolean($key))
			return (bool) $this->getValue($key);
		else
			return $this->setValue($key, is_bool($def) ? $def : null);
	}

	public function getArray($key, $def = null) {
		if ($this->isValidArray($key))
			return (array) $this->getValue($key);
		else
			return $this->setValue($key, is_array($def) ? $def : null);
	}

	public function isValidString($key) {
		return is_string($val = $this->getValue($key)) && strlen($val);
	}

	public function isValidNumber($key, $min = PHP_INT_MIN, $max = PHP_INT_MAX) {
		return is_numeric($val = $this->getValue($key)) && $val >= $min && $val <= $max;
	}

	public function isValidBoolean($key) {
		return is_bool($this->getValue($key));
	}

	public function isValidArray($key) {
		return is_array($val = $this->getValue($key)) && strlen($val);
	}

	public function getStoragePath() {
		return $this->path;
	}

	public function save() {
		return file_put_contents($this->path, json_encode($this->data));
	}

}
?>
