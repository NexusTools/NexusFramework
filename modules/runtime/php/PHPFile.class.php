<?php
class PHPFile extends CachedFile {

	public function __construct($path) {
		parent::__construct($path);
	}

	public function getMimeType() {
		return "text/x-php";
	}

	public function getPrefix() {
		return "php";
	}

	public function getClasses() {
		$this->getValue("classes");
	}

	private function stripSpace($parts) {
		return "<pre>".print_r($parts, true)."</pre>";
	}

	private function parsePHP($block) {
		// Parse Content

		// Strip Comments
		$block = preg_replace("|//.*|S", "", $block[1]);
		$block = preg_replace("|/\*.*?\*/|Ss", "", $block);

		// remove unneeded whitespace
		//$block = preg_replace("/\s+/", " ", $block);

		return "<pre>".$block."</pre>";
	}

	protected function update() {
		$data = Array("classes" => Array(), "methods" => Array(), "defines" => Array());
		$file = file_get_contents($this->getFilepath());
		//header("Content-Type: text/plain");
		//echo "Parsing " . $this->getFilePath() . "\n";
		// Process HTML
		$file = preg_replace("/<\!(\-\-.*?\-\-)?>/Ss", "", $file);

		// Process PHP
		echo preg_replace_callback("/<\?(?:php)?[\s\n]*((?:[^\"']|\".*?\"|'.*?')*?)[\s\n]*\?>/Ss", Array($this, "parsePHP"), $file);
		exit;
	}

}
?>
