<?php
class OutputCapture extends OutputFilter {

	private $outputBuffer;
	
	public function __construct($autoStart=true){
		if($autoStart)
			$this->begin();
	}
	
	public function serve($mimeType =false) {
		Framework::serveData($this->finish(), $mimeType);
	}
	
	public function serveRaw() {
		Framework::serveRawData($this->finish());
	}
	
	public function getOutput() {
		return $this->outputBuffer;
	}
	
	public function begin() {
		if(!$this->start())
			throw new Exception("Failed to start output buffer for unknown reason.");
	}
	
	public function finish() {
		$this->end();
		return $this->outputBuffer;
	}
	
	protected function __start() {
		$this->outputBuffer = "";
	}
	
	protected function __filterData($data) {
		$this->outputBuffer .= $data;
	}
	
	protected function __stop() {}
	
	
}
?>
