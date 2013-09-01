<?php
class LocalFile extends FileLocker {

	// Enum OpenMode
	const ReadOnly = 0x0;
	const ReadWrite = 0x1;

	public function __construct(/* String */ $path, /* self::OpenMode */ $autoOpen = false) {
		FileLocker::__construct($path);
		if($autoOpen !== false)
			$this->open($autoOpen);
	}
	
	public function setContent(/* String */ $content, $autoClose =true, $encode =true) {
		if(!$this->reopen(self::ReadWrite))
			IOException::throwWriteError($this->getFileName());
			
		if($encode)
			$content = json_encode($content);
		$this->write($content);
		
		if($autoClose)
			$this->close();
	}
	
	public function getContent($decode =true, $autoClose =true, $default =false) {
		if(!$this->exists())
			return $default;
			
		if(!$this->reopen(self::ReadOnly))
			IOException::throwReadError($this->getFileName());
			
		$content = fread($this->getResource(), $this->size());
		if($decode)
			$content = json_decode($content, true);
			
		if($autoClose)
			$this->close();
			
		return $content;
	}

    public static function __callStatic($name, $arguments)
    {
    	if(endsWith($name, "To"))
    		$end = 2;
    	else if(endsWith($name, "For"))
    		$end = 3;
    	else if(endsWith($name, "From"))
    		$end = 4;
    	else
    		throw new Exception("Call to unknown method \"" . __CLASS__ . "::$name\"");
    		
        $name = substr($name, 0, strlen($name)-$end);
        $file = new LocalFile(array_shift($arguments));
        return call_user_func_array(Array($file, $name), $arguments);
    }
	
	public function write($content, $pos =0, $length =0) {
		if(!$length)
			$length = strlen($content);
		if($pos)
			$content = substr($content, $pos);
		return fwrite($this->getResource(), $content, $length);
	}
	
	public function read($length) {
		$ret = fread($this->getResource(), $length);
		if($ret == false)
			IOException::throwEOFError();
		return $ret;
	}
	
	public function open(/* self::OpenMode */ $mode) {
		return $this->lock($mode == self::ReadWrite);
	}
	
	public function reopen(/* self::OpenMode */ $mode) {
		return $this->relock($mode == self::ReadWrite);
	}
	
	public function refresh() {
		clearstatcache(false, $this->getFileName());
	}
	
	public static function clearStatCache() {
		clearstatcache();
	}
	
	public function exists() {
		return file_exists($this->getFileName());
	}
	
	public function touch() {
		return touch($this->getFileName());
	}
	
	public function isReadable() {
		return is_readable($this->getFileName());
	}
	
	public function isWritable() {
		return is_writable($this->getFileName());
	}
	
	public function isExecutable() {
		return is_executable($this->getFileName());
	}
	
	public function modificationTime() {
		return filemtime($this->getFileName());
	}
	
	public function isFile() {
		return is_file($this->getFileName());
	}
	
	public function isDir() {
		return is_dir($this->getFileName());
	}
	
	public function isLink() {
		return is_link($this->getFileName());
	}
	
	public function isOpen() {
		return $this->isLocked();
	}
	
	public function size() {
		if($this->isOpen()) {
			$pos = $this->pos();
			$this->seek(0, SEEK_END);
			$size = $this->pos();
			$this->seek($pos);
			return $size;
		}
		
		return filesize($this->getFileName());
	}
	
	public function remaining() {
		return $this->isOpen() ? $this->size() - $this->pos() : false;
	}
	
	public function seek($pos, $whence = SEEK_SET) {
		return fseek($this->getResource(), $pos, $whence);
	}
	
	public function pos() {
		return ftell($this->getResource());
	}
	
	public function close() {
		return $this->unlock();
	}

}
?>
