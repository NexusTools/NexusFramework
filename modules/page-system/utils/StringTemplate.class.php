<?php
class StringTemplate {

	private static $allowEval;
	private static $globals;
	private $string;
	
	public static function indexOfPreg($preg, $string, $offset=0){
		if(preg_match($preg, $string, $matches, PREG_OFFSET_CAPTURE, $offset))
			return $matches[0];
		else
			return false;
	}
	
	public static function __processInputPart($matches){
		return self::__processInputPart0(preg_replace_callback("/\{\s*([^}]+)\s*\}/", Array(__CLASS__, "__processInputPart0"), trim($matches[1])));
		//return self::__processInputPart0(trim($matches[1]));
	}
	
	public static function __processInputPart0($match){
		if(is_array($match))
			$match = $match[1];
		$value = "";
	
		if(startsWith($match, "/"))
			$value = Framework::getReferenceURI(substr($match, 1));
		else if(isset(self::$globals[$match]))
			$value = self::$globals[$match];
		else if(defined($match))
			$value = constant($match);
		else if(isset($_SERVER[$match]))
			$value = $_SERVER[$match];
		else if(self::$allowEval && preg_match("/^\w+(::\w+)?+\s*\(/", $match))
			$value = Runtime::evaluate($match);
		else if(preg_match("/^(\w+)\[([\w|\d]+)\]/", $match, $matches)) {
			$value = self::$globals[$matches[1]][$matches[2]];
			$start = strlen($matches[0]);
			while(preg_match("/\[([\w|\d|\-]+)\]/", $match, $matches, PREG_OFFSET_CAPTURE, $start)) {
				if($matches[0][1] != $start)
					break;

				$value = $value[$matches[1][0]];
				$start += strlen($matches[0][0]);
			}
		}
		
		if(is_bool($value))
			return $value ? "true" : "false";
		else if(is_array($value) || is_object($value))
			return json_encode($value);
		else
			return $value;
	}
	
	public static function processVariables(&$variables, &$constants){
    	foreach($variables as &$variable){
    		if(is_string($variable) && startsWith($variable, '$')) {
    			$variable = substr($variable, 1);
    			if(preg_match("/^(\w+)(\[([\w|\d]+)\])?/", $variable, $matches)) {
    				if($variable == "runtime")
    					$value = $constants;
    				else
						$value = $constants[$matches[1]];
					$start = strlen($matches[1]);
					while(preg_match("/\[([\w|\d|\-]+)\]/", $variable, $matches, PREG_OFFSET_CAPTURE, $start)) {
						if($matches[0][1] != $start)
							break;

						$value = $value[$matches[1][0]];
						$start += strlen($matches[0][0]);
					}
					
					$variable = $value;
				}
    		}
    	}
    	
    	return $variables;
    }
	
	private static function interpolate0($string){
		return preg_replace_callback("/\{\{\s*([^}]+(\s*\(.+?\))?)\s*\}\}/", Array(__CLASS__, "__processInputPart"), $string);
	}
	
	public static function interpolate($string, $allowEval=false, $globals=Array()){
		self::$globals = $globals;
		self::$allowEval = $allowEval;
		
		if(is_array($string))
			foreach($string as &$entry)
				$entry = self::interpolate0($entry);
		else
			$string = self::interpolate0($string);
		
		return $string;
	}

	public function __construct($string){
		$this->string = $string;
	}
	
	public function run($allowEval=false, $globals=Array()){
	}

}
/*
$__interpolate_functions = Array();
$__interpolate_allowEval = Array();
$__interpolate_vars = Array();

function interpolate_callback($match){
	global $__interpolate_allowEval;
	global $__interpolate_vars;
	global $__interpolate_functions;
	
	
}

function interpolate($string, $allowEval=true, $vars=Array(), $pattern="") {
	global $__interpolate_allowEval;
	global $__interpolate_vars;
	
	 $__interpolate_vars = $vars;
	$__interpolate_allowEval = $allowEval;
	
}
*/
?>
