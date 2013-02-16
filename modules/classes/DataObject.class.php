<?php
class DataObject {

	private $knownKeys = Array();
	private $loadedData = Array();
	private $trashedKeys = Array();
	private $saveMarked = false;
	private $path;
	
	public function __construct($path){
		$this->path = fullpath($path) . DIRSEP;
		if(!is_dir($this->path) && !mkdir($this->path))
			throw new IOException($this->path, IOException::Corrupt, '`' . $this->path . "` Corrupt");
		else
			$this->knownKeys = unserialize(file_get_contents($this->path . ".keymap"));
		if(!is_array($this->knownKeys))
			$this->knownKeys = Array();
	}
	
	public function getKeys(){
		$keys = $this->knownKeys;
		foreach(array_keys($this->loadedData) as $key)
			$keys[$key] = true;
		return $keys;
	}
	
	public function __save() {
		file_put_contents($this->path . ".keymap", serialize($this->getKeys()));
		foreach($this->loadedData as $key => $val)
			file_put_contents($this->path . (string)$key, serialize($val));

		foreach(array_keys($this->trashedKeys) as $key)
			unlink($this->path . (string)$key);
	}
	
	public function __get($key){
		if(array_key_exists($key, $this->loadedData))
			return $this->loadedData[$key];
	
		if(array_key_exists($key, $this->knownKeys))
			unset($this->knownKeys[$key]);
			
		$this->loadedData[$key] = unserialize(file_get_contents($this->path . $key));
		return $this->loadedData[$key];
	}
	
	public function __set($key, $val){
		if(array_key_exists($key, $this->knownKeys))
			unset($this->knownKeys[$key]);
			
		if(array_key_exists($key, $this->trashedKeys))
			unset($this->trashedKeys[$key]);
			
		$this->loadedData[$key] = $val;
		if(!$this->saveMarked){
			$this->saveMarked = true;
			register_shutdown_function(Array($this, "__save"));
		}
	}
	
	public function __isset($key){
		return array_key_exists($key, $this->knownKeys) || array_key_exists($key, $this->loadedData);
	}
	
	public function __unset($key){
		if($trash = array_key_exists($key, $this->knownKeys))
			unset($this->knownKeys[$key]);
		if($trash = array_key_exists($key, $this->loadedData))
			unset($this->loadedData[$key]);
		$this->trashedKeys[$key] = true;
		
		if(!$this->saveMarked){
			$this->saveMarked = true;
			register_shutdown_function(Array($this, "__save"));
		}
	}

}
?>
