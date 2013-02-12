<?php
class Lockable {

    private $lockFile;
	private $hasLock;
	
	protected function init($file){
	    $this->lockFile = $file;
	    $this->hasLock = false;
	}
	
	public function lock(){
	    while(!$this->tryLock()){
	        sleep(1);
	    }
	}
	
	public function tryLock(){
	    clearstatcache(false, $this->lockFile);
	    if(file_exists($this->lockFile))
	        return false;
	    if(!touch($this->lockFile))
	        throw new Exception("Permission Required to Use Lock Folder");
	    $this->hasLock = true;
	    return true;
	}
	
	public function unlock(){
	    if(!$this->hasLock)
	        throw new Exception("This instance doesn't have the active lock.");
	    if(file_exists($this->lockFile) && !unlink($this->lockFile))
	        throw new Exception("FAILED TO UNLOCK SERVER, LOCKS ARE DAMAGED WEBSITE BROKEN");
	}
	
}
?>
