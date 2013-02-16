<?php
abstract class CachedObject {

	private static $instanceCount = 0;
	private static $blacklist = false;
	protected $storageObject = false;
	protected $metaObject = false;
	private $storagePath = false;
	private $metaPath = false;
	private $basePath = false;
	
	/*
		Returns the storage ID for this object.
	*/
	public abstract function getID();

	/*
		Returns Cache Prefix used for Storing this Object.
	*/
	public abstract function getPrefix();
	
	/*
		Returns the minimum amount of time that must pass
		before this object can be invalidated.
	*/
	protected abstract function getLifetime();
	
	/*
		Checks if this object is outdated.
	*/
	protected abstract function needsUpdate();
	
	/*
		Populates the Meta-Object.
	*/
	protected abstract function updateMeta(&$metaObject);
	
	/*
		Updates this object's storage.
	*/
	protected abstract function update();
	
	/*
		Whether or not this object is still valid.
		When an object becomes invalid, the cronjob can remove it.
	*/
	public abstract function isValid();
	
	public function getMetaValue($key){
		return $this->metaObject[$key];
	}
	
	public static function countInstances(){
		return self::$instanceCount;
	}
	
	/*
		Attempts to load the cache meta-object.
	*/
	private function loadMeta(){
		if($this->metaObject)
			return;
			
		self::$instanceCount++;
			
		$this->updatePaths();
		if(file_exists($this->metaPath))
			$this->metaObject = unserialize(file_get_contents($this->metaPath));
		
		$overtime = $this->metaObject['n'] <= time();
	
	
		if(!self::$blacklist) {
			self::$blacklist = Array();
			if(array_key_exists("__nocache__", $_GET)) {
				$wildcard = preg_quote(preg_quote("*"));
				
				foreach(explode(",", $_GET['__nocache__']) as $filter) {
					$filter = preg_quote($filter);
					$filter = preg_replace("/" . $wildcard . "/", ".*", $filter);
					array_push(self::$blacklist, "/^$filter$/");
				}
				if(!count(self::$blacklist))
					array_push(self::$blacklist, "/^.+$/");
				
			}
		}
				
		
		if(count(self::$blacklist)) {
			foreach(self::$blacklist as $filter) {
				if(preg_match($filter, $this->getPrefix())) {
					$updateNow = true;
					break;
				}
			}
		} else
			$updateNow = false;
		
		if(!$updateNow) {
			if(!$overtime)
				$updateNow = false;
			else
				$updateNow = $this->needsUpdate();
		}
		
		if($updateNow || !is_array($this->metaObject) || !file_exists($this->storagePath)) {
			$this->metaObject = Array("u" => time(), "n" => time() + $this->getLifetime(),
									  "pv" => method_exists($this, "getProvider") ? $this->getProvider() : get_class($this));
			try {
				$this->storageObject = $this->update();
			}catch(Exception $e){
				if(!file_exists($this->storagePath))
					$this->storageObject = $e;
			}
			
			if($this->storageObject){
				if(is_string($this->storageObject)) {
					$this->metaObject['rs'] = true;
					file_put_contents($this->storagePath, $this->storageObject);
				} else
					file_put_contents($this->storagePath, serialize($this->storageObject));
			}
			
			$this->updateMeta($this->metaObject);
			file_put_contents($this->metaPath, serialize($this->metaObject));
		} else if($overtime) {
			$this->metaObject['n'] = time() + $this->getLifetime();
			file_put_contents($this->metaPath, serialize($this->metaObject));
		}
	}
	
	public function getReferenceURI($mime_type=false, $realFilename=false){
	    if(!$mime_type && method_exists($this, "getMimeType"))
	        $mime_type = $this->getMimeType();
	    return Framework::getReferenceURI($this->getStoragePath(), $mime_type, $realFilename, $this->isShared());
	}
	
	public function getReferenceURL($mime_type=false, $realFilename=false){
	    if(!$mime_type && method_exists($this, "getMimeType"))
	        $mime_type = $this->getMimeType();
	    return Framework::getReferenceURL($this->getStoragePath(), $mime_type, $realFilename, $this->isShared());
	}
	
	private function load(){
		$this->loadMeta();
		if($this->storageObject !== false)
			return;
		
		if(isset($this->metaObject['ds']))
			$this->storageObject = file_get_contents($this->storagePath);
		else if(isset($this->metaObject['rs']))
			$this->storageObject = file_get_contents($this->storagePath);
		else
			$this->storageObject = unserialize(file_get_contents($this->storagePath));
	}
	
	private function updatePaths(){
		if($this->storagePath)
			return;
			
		$this->storagePath = Framework::getTempFolder($this->getPrefix(), $this->isShared());
		if(!is_dir($this->storagePath))
			mkdir($this->storagePath, 0777, true);
			
		$this->storagePath .= $this->getID();
		$this->metaPath = $this->storagePath . ".meta.dat";
		$this->storagePath .= ".dat";
	}
	
	public function nextUpdate(){
		$this->loadMeta();
		
		return $this->metaObject['n'];
	}
	
	/*
		Returns a unix timestamp indicating when this
		object was last updated.
	*/
	public function lastUpdated(){
		$this->loadMeta();
		
		return $this->metaObject['u'];
	}
	
	public function invalidate(){
		unlink($this->metaPath);
		unlink($this->storagePath);
	}
	
	public function getValue($key){
		$this->load();
		return $this->storageObject[$key];
	}
	
	public function hasKey($key){
		$this->load();
		return isset($this->storageObject[$key]);
	}
	
	protected abstract function isShared();
	
	public function getData(){
		$this->load();
		
		if(is_object($this->storageObject) &&
				method_exists($this->storageObject, "toArray"))
				return $this->storageObject->toArray();
		else
			return $this->storageObject;
	}
	
	/*
		Dumps the internal storage into the output buffer via echo
	*/
	public function dump(){
		echo $this->getData();
	}
	
	public function getStoragePath(){
		$this->loadMeta();
		return $this->storagePath;
	}

}
?>
