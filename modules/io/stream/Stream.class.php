<?php
interface Stream {

	const SEEK_SET = 0x0; // Absolute
	const SEEK_REL = 0x1; // Relative
	const SEEK_END = 0x2; // From End
	
	public function pos();
	public function seek($pos, $mode =self::SEEK_SET);
	
	public function read($len, &$data);
	public function write($data, $len =-1, $offset =0);

	public function isOpen();
	public function isReadable();
	public function isWritable();
	
	public function getRequest();
	public function getResponse();

}
?>
