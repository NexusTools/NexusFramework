<?php
abstract class Tree {
	protected $root;
	protected $branch;
	
	public function __construct($data){
		$this->data = $data;
		$this->group = &$this->data;
	}
	
	public function resetGroups() {
		
	}
	
	public function enterGroup($name) {
		
	}
	
	public function hasKey($key){
		return isset($this->data[$key]);
	}
	
	public function value($key){
		return $this->data[$key];
	}
	
	public function insert($key, $value){
		return $this->data[$key];
	}
	
}

?>
