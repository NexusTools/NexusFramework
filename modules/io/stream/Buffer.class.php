<?php
class Buffer implements Stream {

	private $pos = 0;
	private $buffer = "";
	
	public function pos() {
		return $pos;
	}
	
	public function seek($pos, $mode =self::SEEK_SET) {
		switch($mode) {
			case self::SEEK_REL:
				$pos = $this->pos + $pos;
				break;
				
			case self::SEEK_END:
				$pos = strlen($this->buffer) - $pos;
				break;
		}
		if($pos < 0 || $pos > strlen($this->buffer))
			throw new OutOfBoundsException("Attempt to seek beyond buffers boundries", $pos < 0 ? 0 : 1);
			
		$this->pos = $pos;
	}
	
	public function read($len, &$data) {
		
	}
	
	public function write($data, $len =-1, $offset =0) {
	}

	public function isOpen() {
	}
	
	public function isReadable() {
	}
	
	public function isWritable() {
	}
	
	public function getRequest() {
		return Array();
	}
	
	public function getResponse() {
		return Array("Content-Length" => strlen($this->buffer));
	}
	
	public function getBuffer() {
		return $this->buffer;
	}
	
	public function getSize() {
		return strlen($this->buffer);
	}
	
}
?>
