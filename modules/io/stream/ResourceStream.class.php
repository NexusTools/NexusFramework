<?php
class ResourceStream implements Stream {

	private $res;
	
	public function __construct($res) {
		if(!is_resource($res))
			throw new InvalidArgumentException("Expected resource, got " . gettype($res));
			
		
	}

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
