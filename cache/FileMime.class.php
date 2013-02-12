<?php
class FileMime extends CachedFile {

	public function __construct($path){
		CachedFile::__construct($path);
	}
	
	protected function isShared(){
		return true;
	}
	
	public function getMimeType(){
		return "text-php/x-file-mime";
	}
	
	protected function update(){
		if(function_exists("mime_content_type"))
		    return mime_content_type($this->getFilepath());
	    else {
		    $finfo = finfo_open(FILEINFO_MIME, "/usr/share/misc/magic.mgc");
		    $mime_type = finfo_file($finfo, $this->getFilepath());
		    finfo_close($finfo);
		    return $mime_type;
	    }
	}
	
	public function getPrefix(){
		return "file-mimes";
	}
	
}
?>
