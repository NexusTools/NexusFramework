<?php
class FileHashes extends CachedFile {

	public function __construct($path){
		CachedFile::__construct($path);
	}
	
	protected function isShared(){
		return true;
	}
	
	public function getMimeType(){
		return "text-php/x-file-hashes";
	}
	
	protected function update(){
		return Array("md5" => md5_file($this->getFilepath()),
					"sha1" => sha1_file($this->getFilepath()));
	}
	
	public function getPrefix(){
		return "file-hashes";
	}
	
}
?>
