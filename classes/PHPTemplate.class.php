<?php
class ArgInitInstruction extends RuntimeInstruction {

	public function run($program, $caller) {
	}

}

class PHPTemplate extends CachedFile {

	public function __construct($template){
		parent::__construct($template);
	}

	public function getMimeType(){
		return "";
	}
	
	public function getPrefix(){
		return "template";
	}

	public function update(){
		$fileData = file_get_contents($this->getFilepath());
		
		echo $fileData;
		$fileData = preg_replace("/}}\n/", "}}", $fileData);
		$fileData = preg_replace("/\n{{/", "{{", $fileData);
		
		try {
			$compiler = new RuntimeProgramCompiler();
		
			$gotoFrames = Array();
			$instructions = Array();
			$start = 0;
			$last = 0;
			$line = 0;
			while(preg_match("/{{([\w\-]+)\:([^}]+)}}/", $fileData, $matches, PREG_OFFSET_CAPTURE, $start)) {
				$start = $matches[0][1];
				
				print_r($matches);
				
				$data = substr($fileData, $last, $start - $last);
				$lines = preg_match_all("/[\n\r]/", $data, $omatches);
				if(strlen(trim($data)))
					$compiler->addInstruction(new EchoInstruction($data), $line);
				$line+=$lines;
				
				$start += strlen($matches[0][0]);
				$last = $start;
			
				$instruction = false;
				$arguments = Runtime::parseFuncArgs($matches[2][0]);
				$command = $matches[1][0];
				switch($command){
					case "data-init":
						//$instruction = new ArgInitInstruction($arguments);
						break;
					
					case "data-req":
						$compiler->addInstruction(new AssertInstruction("array_key_exists", Array($arguments[0], '$runtime')), $line);
						$compiler->addInstruction(new AssertInstruction("is_$arguments[1]", Array('$' . $arguments[0])), $line);
						break;
					
					case "if":
						if($arguments[0] == "endif")
							$compiler->endBlock();
						else if($arguments[0] == "elseif") {
							array_shift($arguments);
							$compiler->endBlock();
							$compiler->startBlock(array_shift($arguments), $arguments, $line);
						} else
							$compiler->startBlock(array_shift($arguments), $arguments, $line);
						break;

					case "foreach":
						//if($arguments[0] == "end")
						//	$compiler->endLoopBlock();
						//else
						//	$compiler->endLoopBlock(array_shift($arguments), $arguments, $line);
						break;
						
					case "const":
						break;
						
					case "routine":
						break;
						
					case "call":
						break;

					default:
						throw new Exception("Unknown Command: `$command`");
						break;
				}
			}
			
			$data = substr($fileData, $last);
			if(strlen(trim($data)))
				$compiler->addInstruction(new EchoInstruction($data), $line);
			
			$compiled = $compiler->compile();
			print_r($compiled);
			return $compiled;
		}catch(Exception $e){
			print_r($e);
		}
		
		die();
	}
	
	public function generate($constants){
		return $this->getData()->run(array_merge($constants, Array("runtime" => $constants)));
	}
	
	public static function compile($template, $constants){
		$instance = new PHPTemplate($template);
		return $instance->generate($constants);
	}

}
?>
