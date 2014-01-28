<?php
class CacheStorage {

	private static $memCache = array();
	private $storageFile;
	
	public static function instance($key, $section =false) {
		if(!array_key_exists())
			return (self:$memCache[$key] = new CacheStorage($key, $section));
		$this->storageFile = new LocalFile();
	}

	protected CacheStorage($key, $section ="Misceleneous") {
		$this->storageFile = new LocalFile();
	}
	
	public static 

}
?>
