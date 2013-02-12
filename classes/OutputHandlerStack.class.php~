<?php
class OutputHandlerStack {

	private static $outputHandlerStack = Array();
	private static $currentOutputHandler = false;
	private static $pushPop;
	private static $oldOutput = false;
	
	public static function stop($flush=true){
	    while(ob_get_level() > NATIVE_OB_LEVEL)
	        if($flush)
	            ob_end_flush();
	        else
	            ob_end_clean();
	}
	
	public static function init(){
		if(DEBUG_MODE) {
		    if(!is_dir($basePath = INDEX_PATH . "debug"))
		        mkdir($basePath, 0777, true);
		    self::$pushPop = fopen($basePath . DIRSEP . str_replace("/", "_", REQUEST_URI) . ".output.txt", "w");
		    fwrite(self::$pushPop, "NexusFramework Output Handler\n" . date(DATE_RFC822) . "\n----------------\n\n");
		}
		
		// Stop the null buffer
		while(ob_get_level() > NATIVE_OB_LEVEL)
		    ob_end_clean();
		
		ob_start("OutputHandlerStack::handleOutput");
		self::ignoreOutput();
	}
	
	public static function writeEchoLog($content){
	    if(!$content)
	        return;
	    fwrite(self::$pushPop, "Echo Data\n"
		    . json_encode($content,JSON_PRETTY_PRINT) . "\n");
	    foreach(debug_backtrace() as $frame) {
		    if(isset($frame['file']))
			    fwrite(self::$pushPop, "$frame[file]:$frame[line]\n");
	    }
	    fwrite(self::$pushPop, "----------------\n\n");
	}
	
	public static function writeDebugLog($content){
	    if(!$content || !DEBUG_MODE)
	        return;
	    fwrite(self::$pushPop, "Debug Output\n$content\n");
	    foreach(debug_backtrace() as $frame) {
		    if(isset($frame['file']))
			    fwrite(self::$pushPop, "$frame[file]:$frame[line]\n");
	    }
	    fwrite(self::$pushPop, "----------------\n\n");
	}
	
	public static function ignoreOutput(){
	    if(DEBUG_MODE)
	        self::pushOutputHandler("OutputHandlerStack::writeEchoLog");
	    else
	        self::pushOutputHandler("OutputHandlerStack::ignore");
	}
	
	public static function ignore($content){}
	
	public static function handleOutput($output){
	    if(!$output)
	        return;
	    if(self::$oldOutput == $output){
	        if(DEBUG_MODE) {
		        fwrite(self::$pushPop, " -- BUFFER DUPLICATION -- \n$output\n");
		        foreach(debug_backtrace() as $frame) {
			        if(isset($frame['file']))
				        fwrite(self::$pushPop, "$frame[file]:$frame[line]\n");
		        }
		        fwrite(self::$pushPop, "----------------\n\n");
		    }
	        return;
	    }
	    self::$oldOutput = $output;
	    if(DEBUG_MODE) {
		    fwrite(self::$pushPop, "Handling Output\n$output\n");
		    foreach(debug_backtrace() as $frame) {
			    if(isset($frame['file']))
				    fwrite(self::$pushPop, "$frame[file]:$frame[line]\n");
		    }
		    fwrite(self::$pushPop, "----------------\n\n");
		}
	
		if(self::$currentOutputHandler)
			call_user_func(self::$currentOutputHandler, $output);
		else
			return $output;
		return "";
	}
	
	public static function setBufferEnabled($en){
		if($en)
			self::pushOutputHandler("OutputHandlerStack::identity");
		else
			self::popOutputHandler();
	}
	
	public static function pushOutputHandler($handler){
	    if(DEBUG_MODE) {
		    fwrite(self::$pushPop, "Pushing Output Handler\n"
			    . self::prettyHandlerName($handler) . "\n");
		    foreach(debug_backtrace() as $frame) {
			    if(isset($frame['file']))
				    fwrite(self::$pushPop, "$frame[file]:$frame[line]\n");
		    }
		    fwrite(self::$pushPop, "----------------\n\n");
		}
		self::cleanBuffer();
		array_push(self::$outputHandlerStack, self::$currentOutputHandler);
		self::$currentOutputHandler = $handler;
	}
	
	private static function prettyHandlerName($handler){
	    if(is_object($handler))
	        return get_class($handler);
	    else if(is_array($handler)) {
	        $string = "";
	        foreach($handler as $part)
	            $string .= self::prettyHandlerName($part) . "::";
	        return substr($string, 0, strlen($string) - 2);
	    } else if(is_string($handler))
	        return $handler;
	    return "INVALID HANDLER PASSED";
	}
	
	public static function popOutputHandler(){
		self::cleanBuffer();
		if(count(self::$outputHandlerStack))
			self::$currentOutputHandler = array_pop(self::$outputHandlerStack);
		else
			self::$currentOutputHandler = false;
		if(DEBUG_MODE) {
		    fwrite(self::$pushPop, "Popping Output Handler\n"
			    . self::prettyHandlerName(self::$currentOutputHandler) . "\n");
		    foreach(debug_backtrace() as $frame) {
			    if(isset($frame['file']))
				    fwrite(self::$pushPop, "$frame[file]:$frame[line]\n");
		    }
		    fwrite(self::$pushPop, "----------------\n\n");
		}
		
	}
	
	public static function cleanBuffer(){
		self::handleOutput(ob_get_contents());
		ob_clean();
		if(ob_get_length()) {
		    ob_end_clean();
		    die("SEVERE INTERNAL PHP MALFUNCTION");
		}
	}

}
?>
