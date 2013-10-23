<?php
class ReferenceMap {

	private $map = Array();

	public function setValue($key, $val) {
		return $this->map[$key] = __refmap_weakref_create($val);
	}

	public function getValue($key) {
		if (array_key_exists($key, $this->map))
			return __refmap_weakref_get($this->map[$key]);
		else
			return null;
	}

	public function containsKey($key) {
		return array_key_exists($key, $this->map);
	}

	public function isKeyValid($key) {
		if (array_key_exists($key, $this->map))
			return __refmap_weakref_valid($this->map[$key]);
		else
			return false;
	}

}
if (class_exists("Weakref", false)) {
	function __refmap_weakref_create($obj) {
		if (is_object($obj))
			return new Weakref($obj);

		return $obj;
	}
	function __refmap_weakref_get($ref) {
		if (($ref instanceof Weakref) && $ref->valid())
			return $ref->get();
		return null;
	}
	function __refmap_weakref_valid($ref) {
		return ($ref instanceof Weakref) && $ref->valid();
	}
} else {
	function __refmap_weakref_create($obj) {
		return $obj;
	}
	function __refmap_weakref_get($ref) {
		return $ref;
	}
	function __refmap_weakref_valid($ref) {
		return true;
	}
}
?>
