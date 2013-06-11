<?php
class FileHashes extends CachedFile {

	private static $instances;
	
	public static function __init() {
		self::$instances = new ReferenceMap();
	}
	
	public static function hash($method, $path) {
		if($method == "md5")
			return self::md5($path);
		else if($method == "md5")
			return self::md5($path);
		throw new Exception("Unsupported hashing method: $method");
	}
	
	public static function md5($path) {
		$inst = self::instance($path);
		return $inst->getValue("md5");
	}
	
	public static function sha1($path) {
		$inst = self::instance($path);
		return $inst->getValue("sha1");
	}
	
	public static function instance($path) {
		$path = fullpath($path);
		$val = self::$instances->getValue($path);
		if($val)
			return $val;
		
		return self::$instances->setValue($path, new FileHashes($path));
	}

	protected function __construct($path){
		CachedFile::__construct($path);
	}
	
	protected function isShared(){
		return true;
	}
	
	public function getMimeType(){
		return "text-php/x-file-hashes";
	}
	
	protected function update(){
		if(is_dir($this->getFilepath())) {
		} else
			return Array("md5" => md5_file($this->getFilepath()),
						"sha1" => sha1_file($this->getFilepath()));
	}
	
	public function getPrefix(){
		return "file-hashes";
	}
	
} FileHashes::__init();
?>
