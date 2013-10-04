<?php
class FileMime extends CachedFile {

	private static $instances;
	
	public static function __init() {
		self::$instances = new ReferenceMap();
	}
	
	public static function instance($path) {
		$path = fullpath($path);
		$val = self::$instances->getValue($path);
		if($val)
			return $val;
		
		return self::$instances->setValue($path, new FileMime($path));
	}

	protected function __construct($path){
		CachedFile::__construct($path);
	}
	
	protected function isShared(){
		return true;
	}
	
	public function getMimeType(){
		return "text-php/x-file-mime";
	}
	
	protected function update(){
		$mime_type = false;
		
		if(endsWith($this->getFilepath(), ".js"))
			$mime_type = "text/javascript";
		else if(endsWith($this->getFilepath(), ".css"))
			$mime_type = "text/css";
		else if(endsWith($this->getFilepath(), ".htm") ||
				endsWith($this->getFilepath(), ".html"))
			$mime_type = "text/html";
		else {
			if(function_exists("mime_content_type"))
				$mime_type = mime_content_type($this->getFilepath());
			else {
				$finfo = finfo_open(FILEINFO_MIME, "/usr/share/misc/magic.mgc");
				$mime_type = finfo_file($finfo, $this->getFilepath());
				finfo_close($finfo);
			}
	    }
	    
	    if(!$mime_type)
	    	$mime_type = "text/plain";
	    
	    return $mime_type;
	}
	
	public function getPrefix(){
		return "file-mimes";
	}
	
	public static function forFile($file) {
		$mime = FileMime::instance($file);
		return $mime->getData();
	}
	
} FileMime::__init();
?>
