<?php
class DynamicStorageObject {

	private $data;
	private $storagePath;
	
	public function __construct($storagePath){
		if(is_object($storagePath))
			
		$this->storagePath = fullpath($storagePath);
		if(!is_dir($storagePath))
			mkdir($storagePath)
	}
	
	public function setValue($key, $data){
	}
	
	public function getValue($key, $def=null){
	}
	
	public function delValue($key){
	}
	
	public function createBranch($name){
		$this->data[$key] = new DynamicStorageObject(this);
	}
	
	public function __sleep() {
		return Array("storagePath");
	}

}
?>
