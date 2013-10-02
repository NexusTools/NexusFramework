<?php
abstract class LazyMap implements Countable, ArrayAccess, SeekableIterator {

	protected $data = false;
	private $keys = false;
	private $pos = 0;

	public function __construct($data) {
		$this->data = $data;
	}
	
	protected abstract function resolveData();
	
	protected function checkStructure() {
		if(!$this->keys) {
			$this->resolveData();
			$this->keys = array_keys($this->data);
		}
	}
	
	// Iteration, only check the structure on rewinds
	public function &current() {
		return $this->data[$this->keys[$this->pos]];
	}
	
	public function key() {
		return $this->keys[$this->pos];
	}
	
	public function next(){
		$this->pos++;
	}
	
	public function seek($pos) {
		$this->pos = $pos;
	}
	
	public function rewind() {
		$this->checkStructure();
		$this->pos = 0;
	}
	
	public function valid() {
		return $this->pos >= 0 && $this->pos < $this->count();
	}
	
	public function offsetExists($offset) {
		$this->checkStructure();
		return array_key_exists($offset, $this->data);
	}
	
	public function offsetGet($offset) {
		$this->checkStructure();
		return $this->data[$offset];
	}
	
	public function offsetSet($offset, $value) {
		$this->checkStructure();
		$this->data[$offset] = $value;
		$this->keys = array_keys($this->data);
	}
	
	public function offsetUnset($offset) {
		$this->checkStructure();
		unset($this->data[$offset]);
		$this->keys = array_keys($this->data);
	}
	
	public function count() {
		$this->checkStructure();
		return count($this->data);
	}
	
	public function getData() {
		$this->checkStructure();
		return $this->data;
	}


}
?>
