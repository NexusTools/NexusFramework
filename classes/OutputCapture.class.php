<?php
class OutputCapture {

	private $outputBuffer;
	
	public function __construct($autoStart=false){
		if($autoStart)
			$this->start();
	}
	
	public function pushOutput($out){
		$this->outputBuffer .= $out;
		return "";
	}
	
	public function getOutput(){
		return $this->outputBuffer;
	}
	
	public function start(){
		$outputBuffer = "";
		OutputHandlerStack::pushOutputHandler(array($this, "pushOutput"));
	}
	
	public function finish(){
		OutputHandlerStack::popOutputHandler();
		return $outputBuffer;
	}
	
}
?>
