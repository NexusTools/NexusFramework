<?php
class EchoInstruction extends RuntimeInstruction {

	private $data;
	
	public function __construct($data){
		$this->data = $data;
	}
	
	public function run($program, $runtime){
		echo interpolate($this->data, false, $runtime);
	}

}
?>
