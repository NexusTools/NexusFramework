<?php
class CallableInstruction extends RuntimeInstruction {

	private $callable;
	private $arguments;

	public function __construct($callable, $arguments) {
		$this->callable = $callable;
		$this->arguments = $arguments;
	}

	public function run($program, $runtime) {
		if (!$this->callable)
			throw new Exception("Invalid Callable");

		call_user_func_array($this->callable, StringTemplate::interpolate($this->arguments, $runtime));
	}

}
?>
