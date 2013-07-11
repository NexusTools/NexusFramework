<?php
abstract class CachedFileBase extends CachedObject {

	private $filepath;
	private $filehash = false;
	
	protected function __construct($path){
		$this->filepath = fullpath($path);
		if(!file_exists($this->getFilepath()))
			throw new Exception("No Such File `". $this->filepath . "`");
	    if(!is_readable($this->getFilePath()))
	        throw new Exception("Cannot Read File `". $this->filepath . "`");
	}
	
	public function getFilepath(){
		return $this->filepath;
	}
	
	public function getHash(){
		return $this->getMetaValue('h');
	}
	
	public function getModificationTime(){
		return $this->getMetaValue('m');
	}
	
	public function isValid(){
		return file_exists($this->getMetaValue('p'));
	}
	
	protected function updateMeta(&$meta){
		$meta['h'] = Framework::fileHash($this->getFilepath());
		$meta['m'] = filemtime($this->getFilepath());
		$meta['p'] = $this->getFilepath();
		$this->updateAdvancedMeta($meta);
	}
	
	protected function needsUpdate(){
		return $this->getHash() != Framework::fileHash($this->getFilepath());
	}

	public function getID(){
		return Framework::uniqueHash($this->filepath . $this->getAdvancedID());
	}
	
	public function getLifetime(){
		return 2 + rand(0, 8); // 2 seconds to 10 seconds
	}
	
	protected abstract function getAdvancedID();
	protected abstract function updateAdvancedMeta(&$meta);
	public abstract function getMimeType();
	
	public function dumpAsResponse($expiresAt =false){
		if($this->etag === false)
			$this->etag = md5($this->combinedtag);
			
		$modtime = Framework::formatGMTDate($this->latestModify);
		if(!$expiresAt) // Default to expiring after between 2 and 15 minutes to try and balance update checking
			$expiresAt = time() + rand(strtotime("+1 week", 0), strtotime("+2 weeks", 0));
		header("Expires: " . Framework::formatGMTDate($expiresAt));
		header("Content-Type: " . $this->getMimeType());
		header("Last-Modified: " . $modtime);
		header("ETag: " . $this->etag);
		
		header_remove("Cache-Control");
		header_remove("Pragma");
		
		$headers = getallheaders();
		if((isset($headers['If-None-Match']) && $headers['If-None-Match'] == $this->etag) ||
				(isset($headers['If-Modified-Since']) && $headers['If-Modified-Since'] == $modtime)) {
			header("HTTP/1.1 304 Not Modified");
			return;
		}
		$this->dump();
	}

}
?>
