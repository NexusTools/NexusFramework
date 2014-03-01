<?php
class CompressedScript extends ScriptCompressor {

	private $filename;

	public function __construct($path) {
		$this->filename = ($path = fullpath($path));
		$this->addScript($path);
	}
	
	public function getFilepath() {
		return $this->filename;
	}

}
?>
