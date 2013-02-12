<?php
class GotoOnFailureInstruction extends RuntimeInstruction {

	private $instruction;
	private $callable;
	private $arguments;

	public function __construct($callable, $arguments){
		$this->callable = $callable;
		$this->arguments = $arguments;
	}
	
	public function setInstruction($pos){
		$this->instruction = $pos;
	}

	public function run($program, $runtime){
		if(!$this->callable)
			throw new Exception("Invalid Callable");
			
		$origArgs = $this->arguments;
		try {
			if(!call_user_func_array($this->callable, StringTemplate::processVariables($this->arguments, $runtime)))
				return $this->instruction;
		}catch(Exception $e){
			print_r($origArgs);
			die();
		}
	}

}
?>
