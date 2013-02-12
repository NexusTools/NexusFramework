<?php
class Settings {
	
	private $path;
	private $data = Array();
	private $marked = false;
	
	public function __construct($extension, $name="settings"){
		$this->path = Framework::getConfigFolder($extension) . "$name.json";
		
		if(file_exists($this->path))
			$this->data = json_decode(file_get_contents($this->path), true);
	    else if(file_exists($legayPath = CONFIG_PATH . "$extension.settings")) {
	        // Upgrade from old settings format
	        $this->data = unserialize(file_get_contents($legayPath));
	        if(!$this->save())
	            throw new Exception("Failed to upgrade `$extension/$name` from old settings format");
	        unlink($legayPath);
	    }
		
		if(!is_array($this->data))
			$this->data = Array();
	}
	
	public function getValue($key, $def=null){
		return array_key_exists($key, $this->data) ? $this->data[$key] : $def;
	}
	
	public function setValue($key, $val){
		$this->data[$key] = $val;
		$this->save();
		return;
		if(!$this->marked){
			$this->marked = true;
			register_shutdown_function(Array($this, "save"));
		}
	}
	
	public function getStoragePath(){
		return $this->path;
	}
	
	public function save(){
		return file_put_contents($this->path, json_encode($this->data));
	}
	
}
?>
