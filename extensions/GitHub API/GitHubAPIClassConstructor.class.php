<?php
class GitHubAPIClassConstructor extends GitHubAPIClassConstructorCachedObject {
	public static $instance;
	private $classes = array();
	public function __construct() {
		GitHubAPIClassConstructorCachedObject::__construct("GitHub-API-Class-Constructor");
	}

	public static function getInstance() {
		if(!self::$instance)
			self::$instance = new GitHubAPIClassConstructor();
		return self::$instance;
	}

	public function loadClasses() {
		if($this->classes)
			return;
		$this->classes = $this->getData();
		foreach($this->classes as $class)
			$this->constructClass($class);
	}

	public function constructClass($class, $varNames=null, $varValues=null) {
		$className = 'GitHub' . $class;
		if(!class_exists($className))
			eval("class $className {}");
		$classNameObj = new $className;

		if($varNames && $varValues) {
			for($i = 0; $i < count($varNames); $i++) {
				$varName = $varNames[$i];
				$classNameObj->$varName = $varValues[$i];
			}
		}

		if(in_array($class, $this->classes, true)) {
			return $classNameObj;
		} else {
			array_push($this->classes, $class);
			$this->invalidate();
			$this->getData();
			return $classNameObj;
		}
	}

	public function update() {
		return $this->classes;
	}
}
?>
