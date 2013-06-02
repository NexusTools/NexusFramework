<?php

class CachedFileSetEntry extends CachedFile {

	private $fileset;

	public function __construct($path, $fileset){
		CachedFile::__construct($path);
		$this->fileset = $fileset;
	}
	
	protected function isShared(){
		return false;
	}
	
	public function getPrefix(){
		return $this->fileset->getPrefix();
	}
	
	public function getMimeType(){
		return $this->fileset->getMimeType();
	}
	
	protected function update(){
		return $this->fileset->processFile($this->getFilepath());
	}
	
	public function getProvider(){
		return $this->fileset->getProvider();
	}

	
}

abstract class CachedFileSet extends CachedObject {

	private $files = Array();
	private $latestModify = 0;
	private $combinedtag = "";
	private $etag = false;

	protected function addFile($path){
		$path = fullpath($path);
		$file = new CachedFileSetEntry($path, $this);
		if($file->lastupdated() > $this->latestModify)
			$this->latestModify = $file->lastupdated();
		$this->combinedtag .= $file->getHash();
		$this->files[$path] = $file;
	}
	
	public function needsUpdate(){
	    foreach($this->files as $file)
	        if($file->needsUpdate()) {
	        	throw new Exception("Needs Update: " . print_r($file, true));
	            return true;
	        }
	    return false;
	}
	
	public function isValid(){
	    foreach($this->files as $file)
	        if(!$file->isValid())
	            return false;
	    return true;
	}
	
	public function update(){
	    $data = "";
	    foreach($this->files as $file)
	        $data .= $file->getData() . "\n";
	    return $data;
	}
	
	public function isShared(){
		foreach($this->files as $file)
			if(!$file->isShared())
				return false;
		return true;
	}
	
	protected function updateMeta(&$meta){}
	
	public function getLifetime(){
		return 2; // 2 seconds to 10 seconds
	}
	
	public function getID(){
	    $combinedID = "";
	    foreach($this->files as $file)
	        $combinedID .= $file->getID();
	    return Framework::uniqueHash($combinedID);
	}
	
	public function dumpAsResponse(){
		if($this->etag === false)
			$this->etag = md5($this->combinedtag);
			
		$modtime = Framework::formatGMTDate($this->latestModify);
		header("Content-Type: " . $this->getMimeType());
		header("Last-Modified: " . $modtime);
		header("ETag: " . $this->etag);
		
		header_remove("Cache-Control");
		header_remove("Expires");
		header_remove("Pragma");
		
		$headers = getallheaders();
		if((isset($headers['If-None-Match']) && $headers['If-None-Match'] == $this->etag) ||
				(isset($headers['If-Modified-Since']) && $headers['If-Modified-Since'] == $modtime)) {
			header("HTTP/1.1 304 Not Modified");
			return;
		}
		$this->dump();
	}
	
	public function getProvider(){
		return get_class($this);
	}
	
	public abstract function processFile($path);
	public abstract function getMimeType();

}
?>
