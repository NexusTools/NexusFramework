<?php
class AssertInstruction extends RuntimeInstruction {

	private $callable;
	private $arguments;

	public function __construct($callable, $arguments){
		$this->callable = $callable;
		$this->arguments = $arguments;
	}
	
	public function run($program, $runtime){
		if(!$this->callable)
			throw new Exception("Invalid Callable");

		if(!call_user_func_array($this->callable, StringTemplate::processVariables($this->arguments, $runtime)))
			throw new Exception("Assertion Failed");
	}

}
?>
