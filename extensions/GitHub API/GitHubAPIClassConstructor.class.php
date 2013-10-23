<?php
class GitHubAPIClassConstructor extends GitHubAPIClassConstructorCachedObject {
	public static $instance;
	private $classes = array();
	public function __construct() {
		GitHubAPIClassConstructorCachedObject::__construct("GitHub-API-Class-Constructor");
	}

	public static function getInstance() {
		if (!self::$instance)
			self::$instance = new GitHubAPIClassConstructor();
		return self::$instance;
	}

	public function loadClasses() {
		if ($this->classes)
			return;
		$this->classes = $this->getData();
		foreach ($this->classes as $class)
			$this->constructClass($class[0], $class[1]);
	}

	public function constructClass($type, $varNames = null, $varValues = null) {
		$className = 'GitHub'.$type;
		if (!class_exists($className))
			eval("class $className {}");
		$classNameObj = new $className;

		if (is_array($varValues)) {
			for ($i = 0; $i < count($varNames); $i++) {
				$varName = $varNames[$i];
				$classNameObj->$varName = $varValues[$i];
			}

			$idx = -1;
			for ($i = 0; $i < count($this->classes); $i++) {
				if ($this->classes[$i][0] == $type) {
					$idx = $i;
					break;
				}
			}

			if ($idx != - 1) {
				$diff = $this->classes[$idx][1] != $varNames;
				if ($diff)
					$this->classes[$idx] = array($type, $varNames);
			} else {
				array_push($this->classes, array($type, $varNames));
				$diff = true;
			}
			if ($diff) {
				$this->invalidate();
				$this->getData();
			}
		}
		return $classNameObj;
	}

	public function update() {
		return $this->classes;
	}
}
?>
