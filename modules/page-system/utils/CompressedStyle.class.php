<?php
class CompressedStyle extends StyleCompressor {

	private $filename;

	public function __construct($path) {
		$this->filename = ($path = fullpath($path));
		$this->addStyle($path);
	}

	public function getFilename() {
		return $this->filename;
	}

}
?>
