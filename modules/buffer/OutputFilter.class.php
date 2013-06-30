<?php
abstract class OutputFilter {

	private $active = false;
	private $level = 0;
	
	public function start() {
		if($this->level)
			return true;
		
		@ob_flush();
		$ret = ob_start(array($this, "__filter"), "4096");
		if($ret)
			$this->level = ob_get_level();

		return $ret;
	}
	
	public static function resetToNative($attachVoid =true) {
		while(ob_get_level() > NATIVE_OB_LEVEL)
			ob_end_flush();
		if($attachVoid)
			ob_start("ob_void", 512);
	}
	
	public function isActive() {
		return $this->active;
	}
	
	public function isStarted() {
		return $this->level > 0;
	}
	
	public function end() {
		if($this->level < 1)
			return true;
		
		if(ob_get_level() != $this->level)
			throw new Exception(get_class($this) . " is not the top-level buffer.");
			
		@ob_flush();
		while($this->level > 0)
			ob_end_flush();
		return true;
	}
	
	public function __filter($data, $phase) {
		if(ob_get_level() != $this->level)
			throw new Exception(get_class($this) . " is not the top-level buffer, do not attach ob_start manually.");
		// PHP_OUTPUT_HANDLER_START
		// PHP_OUTPUT_HANDLER_CONT
		// PHP_OUTPUT_HANDLER_END
		
		if(($phase & PHP_OUTPUT_HANDLER_START) == PHP_OUTPUT_HANDLER_START) {
			if($this->active)
				throw new Exception(get_class() . " received start phase when already started.");
			$this->__start();
			$this->active = true;
		} else if(!$this->active)
			throw new Exception(get_class() . " received $phase phase when not started.");
		if($data && strlen($data)) {
			$this->__filterData($data);
			return $data;
		}
		if(($phase & PHP_OUTPUT_HANDLER_END) == PHP_OUTPUT_HANDLER_END) {
			$this->__stop();
			$this->active = false;
			$this->level = 0;
			return false;
		}
		return "";
	}
	
	protected abstract function __start();
	protected abstract function __filterData($data);
	protected abstract function __stop();
	
}
?>
