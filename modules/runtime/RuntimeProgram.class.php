<?php
class RuntimeProgram {

	private $instructions = Array();
	private $running = false;

	public function __construct($instructions) {
		$this->instructions = $instructions;
	}

	public function stop() {
		$this->running = false;
	}

	public function __sleep() {
		return array('instructions');
	}

	public function run($runtime = false) {
		try {
			$max = count($this->instructions);
			$pos = 0;
			$this->running = true;
			while ($this->running && $pos < $max) {
				$instruction = $this->instructions[$pos];
				$npos = $instruction->run($this, $runtime);
				if (!is_null($npos))
					$pos = $npos;
				else
					$pos++;
			}
		} catch (Exception $e) {
			print_r($e);
			return Array("Message" => $e->getMessage(), "Line" => $instruction->getLine());
		}

		$this->running = false;
		return true;
	}

}
?>
