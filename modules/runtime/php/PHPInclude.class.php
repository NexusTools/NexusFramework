<?php
class PHPInclude extends CachedFile {

	private $outputBuffer;
	
	const PASS_OUTPUT = 0x0;
	const CAPTURE_OUTPUT = 0x1;
	const SILENCE_OUTPUT = 0x2;

	public function __construct($path, $stripOutput=true){
		CachedFile::__construct($path);
		$this->stripOutput = $stripOutput;
	}
	
	public function getMimeType(){
		return "application/x-php";
	}
	
	public function getPrefix(){
		return "php";
	}
	
	public function update(){
		if(true)
			return file_get_contents($this->getFilepath());
	
		if($this->stripOutput)
			$source = "<? ";
		else
			$source = "";
		$handle = @fopen($this->getFilepath(), "r");
		if ($handle) {
			while (($buffer = fgets($handle, 4096)) !== false) {
				while(($pos = strpos($source, "<?")) !== false
						&& ($pos = strpos($source, "<?php")) !== false) {
					if(!$this->stripOutput) {
						$source .= trim(substr($source, $pos));
						$source .= "<? ";
					}
					while (($end = strpos($source, "?>")) !== false) {
						if(!$this->stripOutput) {
							$source .= trim(substr($source, $pos));
							$source .= "<? ";
						}
						if(($buffer = fgets($handle, 4096)) === false)
							break;
					}
					$buffer = trim(substr($buffer, $end+2));
				}
				echo '\n';
			}
			if(strlen($buffer) > 0 && !$this->stripOutput)
				$source .= $buffer;
			fclose($handle);
		} else
			die("Unable to Open");
		
		if($this->stripOutput)
			$source .= " ?>";
		return trim($source);
	}
	
	protected function isShared(){
		return false;
	}
	
	public function getOutput(){
		return $this->outputBuffer;
	}
	
	public function run($__outputmode = self::PASS_OUTPUT, $environment=Array()){
		if($__outputmode != self::PASS_OUTPUT)
			if($__outputmode == self::SILENCE_OUTPUT) {
				OutputHandlerStack::ignoreOutput();
				ob_start("OutputFilter::void");
				$this->outputBuffer = "";
			} else {
				OutputHandlerStack::pushOutputHandler(array($this, "pushOutput"));
				$this->outputBuffer = new OutputCapture();
			}
		try{
		    extract($environment);
			$ret = include($this->getStoragepath());
			if($this->outputBuffer instanceof OutputCapture)
				$this->outputBuffer = $this->outputBuffer->finish();
		}catch(Exception $e){
			if($this->outputBuffer instanceof OutputCapture)
				$this->outputBuffer = $this->outputBuffer->finish();
			$this->outputBuffer .= "<pre>$e</pre>";
			$ret = false;
		}
		if($__outputmode == self::SILENCE_OUTPUT) {
			ob_flush();
			ob_end_clean();
		}
		return $ret;
	}
	
}
?>
