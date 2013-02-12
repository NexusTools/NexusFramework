<?php
class RuntimeProgramCompiler {

	private $instructions = Array();
	private $stack = Array();
	private $filename;
	
	public function __construct($filename="Unknown"){
		$this->filename = $filename;
	}

	public function addInstruction($instruction, $line=0){
		if($line)
			$instruction->setLine($line);
		array_push($this->instructions, $instruction);
	}
	
	public function startBlock($callable, $arguments, $line=0){
		$testInstruction = new GotoOnFailureInstruction($callable, $arguments);
		if($line)
			$testInstruction->setLine($line);
		array_push($this->stack, $testInstruction);
		array_push($this->instructions, $testInstruction);
	}
	
	public function endBlock(){
		array_pop($this->stack)->setInstruction(count($this->instructions));
	}
	
	public function startLoopBlock($callable, $arguments, $line=0){
	}
	
	public function endLoopBlock($line=0){
	}
	
	public function compile(){
		if(count($this->stack))
			throw new Exception("Unclosed Blocks Remain");
		return new RuntimeProgram($this->instructions);
	}

}
?>
