<?php
class FileLocker implements Locker {

	const WOULD_BLOCK = 0;

	private $fileName;
	private $fileRes = false;
	private $exclusive = false;
	
	public function __construct($fileName) {
		$this->fileName = fullpath($fileName);
	}
	
	public function getFileName() {
		return $this->fileName;
	}
	
	protected function getResource() {
		return $this->fileRes;
	}
	
	public function lock($exclusive) {
		if($this->fileRes)
			return true;
			
		if($exclusive)
			$this->fileRes = fopen($this->fileName, "w+");
		else {
			if(!file_exists($this->fileName))
				touch($this->fileName);
			$this->fileRes = fopen($this->fileName, "r");
		}
		if(!$this->fileRes)
			throw new IOException($this->fileName, IOException::WriteAccess);
		
		if(flock($this->fileRes, $exclusive ? LOCK_EX : LOCK_SH)) {
			$this->exclusive = $exclusive;
			return true;
		}
			
		fclose($this->fileRes);
		$this->fileRes = false;
		return false;
	}

	public function relock($exclusive) {
		if($this->isLocked()) {
			if($this->exclusive == $exclusive)
				return true;
			
			if(!$this->unlock())
				return false;
		}
		
		return $this->lock($exclusive);
	}

	public function tryLock($exclusive) {
		if($this->fileRes)
			return true;
		
		if($exclusive)
			$this->fileRes = fopen($this->fileName, "w+");
		else {
			if(!file_exists($this->fileName))
				touch($this->fileName);
			$this->fileRes = fopen($this->fileName, "r");
		}
		if(!$this->fileRes)
			throw new IOException($this->fileName, IOException::WriteAccess);
		
		if(flock($this->fileRes, LOCK_NB | ($exclusive ?
							LOCK_EX : LOCK_SH), $wouldBlock)) {
			$this->exclusive = $exclusive;
			return true;
		}
		
		if($wouldBlock)
			return self::WOULD_BLOCK;
			
		fclose($this->fileRes);
		$this->fileRes = false;
		return false;
	}

	public function isLocked() {
		return !!$this->fileRes;
	}

	public function unlock() {
		if(!$this->fileRes)
			return true;

		if(!flock($this->fileRes, LOCK_UN))
			return false;
			
		fclose($this->fileRes);
		$this->fileRes = false;
		return true;
	}


}
?>
