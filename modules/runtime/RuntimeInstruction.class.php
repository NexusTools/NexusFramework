<?php
abstract class RuntimeInstruction {

	private $line = 0;
	
	public function setLine($line){
		$this->line = $line;
	}
	
	public function getLine(){
		return $this->line;
	}
	
	public abstract function run($program, $caller);	
	
}
?>
