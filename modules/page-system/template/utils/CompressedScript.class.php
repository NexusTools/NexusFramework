<?php
class CompressedScript extends ScriptCompressor {

	public function __construct($path){
		$this->addScript($path);
	}

}
?>
