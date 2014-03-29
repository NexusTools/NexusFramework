<?php
class LocalFile extends FileLocker {

	// Enum OpenMode
	const ReadOnly = 0x0;
	const ReadWrite = 0x1;

	public function __construct( /* String */ $path, /* self::OpenMode */ $autoOpen = false) {
		FileLocker::__construct($path);
		if ($autoOpen !== false)
			$this->open($autoOpen);
	}
	
	public function setRawContent($content, $autoClose = true, $unsafe = false) {
		$this->setContent($content, false, $autoClose, $unsafe);
	}

	public function setContent( /* String */ $content, $encode = true, $autoClose = true, $unsafe = false) {
		if ($unsafe) {
			if (!$this->reopen(self::ReadWrite))
				IOException::throwWriteError($this->getFileName());

			if ($encode) {
				if(!is_string($encode))
					$encode = "json";
				switch($encode) {
					case "serialize":
						$content = serialize($content);
						break;
						
					default:
						$content = json_encode($content);
						break;
				}
			}
			$this->write($content);

			if ($autoClose)
				$this->close();
		} else {
			$this->close();

			$tempFile = new LocalFile($this->getFileName().".tmp");
			$tempFile->setContent($content, $encode, true, true);
			if (!$tempFile->move($this->getFileName(), true))
				IOException::throwWriteError($this->getFileName());
		}
	}
	
	public function getRawContent($autoClose = true, $default = false, $checkFallback = true) {
		return $this->getContent(false, $autoClose, $default, $checkFallback);
	}

	public function getContent($decode = true, $autoClose = true, $default = false, $checkFallback = true) {
		if (!$this->exists())
			return $checkFallback ? LocalFile::getContentFor($this->getFileName()
				.".tmp", $decode, true, $default, false) : $default;

		if (!$this->reopen(self::ReadOnly))
			IOException::throwReadError($this->getFileName());

		$content = fread($this->getResource(), $this->size());
		if ($decode) {
			if(!is_string($decode))
				$decode = "json";
			switch($decode) {
				case "serialize":
					$content = unserialize($content);
					break;
				
				default:
					$content = json_decode($content, true);
					break;
			}
		}
		
		if ($autoClose)
			$this->close();

		return $content;
	}

	public static function __callStatic($name, $arguments) {
		if (endsWith($name, "To"))
			$end = 2;
		else
			if (endsWith($name, "For"))
				$end = 3;
			else
				if (endsWith($name, "From"))
					$end = 4;
				else
					throw new Exception("Call to unknown method \"".__CLASS__."::$name\"");

		$name = substr($name, 0, strlen($name) - $end);
		$file = new LocalFile(array_shift($arguments));
		return call_user_func_array(Array($file, $name), $arguments);
	}

	public function write($content, $pos = 0, $length = 0) {
		if (!$length)
			$length = strlen($content);
		if ($pos)
			$content = substr($content, $pos);
		return fwrite($this->getResource(), $content, $length);
	}

	public function read($length) {
		$ret = fread($this->getResource(), $length);
		if ($ret == false)
			IOException::throwEOFError();
		return $ret;
	}

	public function open( /* self::OpenMode */ $mode) {
		return $this->lock($mode == self::ReadWrite);
	}

	public function reopen( /* self::OpenMode */ $mode) {
		return $this->relock($mode == self::ReadWrite);
	}

	public function move($newpath, $overwrite = false) {
		$newpath = fullpath($newpath);
		if (file_exists($newpath)) {
			if (!$overwrite)
				throw new IOException($newpath, false, "Cannot move file, destination exists");

			if (!unlink($newpath))
				throw new IOException($newpath, false, "Cannot move file, destination won't delete");
		}

		$this->close();
		if (!rename($this->getFileName(), $newpath))
			return false;

		clearstatcache(false, $this->getFileName());
		$this->setFileName($newpath);
		clearstatcache(false, $this->getFileName());
		return true;
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
		if ($this->isOpen()) {
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
