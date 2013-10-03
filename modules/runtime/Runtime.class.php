<?php
class Runtime {
	
	public static function parseFuncArgs($argString){
		$arguments = Array();
		$start = 0;
		while(($result = StringTemplate::indexOfPreg("/([\"',]|$)/", $argString, $start)) !== false) {
			if($result[0] == "'" || $result[0] == '"') {
				$start = $result[1] + 1;
				$length = strpos($argString, $result[0], $result[1]+1) - $start;
				$next = StringTemplate::indexOfPreg("/([,]|$)/", $argString, $start + $length + 1);
				
				array_push($arguments, substr($argString, $start, $length));
				$next = $next[1]+1;
			} else {
				$next = $result[1];
				$length = $next - $start;
				$next++;
				
				array_push($arguments, trim(substr($argString, $start, $length)));
			}
			
			$start = $next;
		}
		array_map("stripslashes", $arguments);
		return $arguments;
	}
	
	protected static function callVirtualFunction($call, $args){
	    if(!is_array($call))
	        switch($call) {
	            case "If":
	                if(count($args) == 2)
	                    array_push($args, "");
	                if(count($args) != 3)
	                    throw new Exception("$call requires 2-3 arguments");
	                    
	                $fork = $args[0];
	                if(!is_string($fork))
	                    throw new Exception("First argument must be string referening constant or callable");
	                if(defined($fork))
	                    $fork = constant($fork);
	                else {
	                    if(preg_match("/^(\w+)::(\w+)$/", $string, $matches))
	                        $fork = Array($matches[1], $matches[2]);
	                    if(!is_callable($fork))
	                        throw new Exception("`$args[0]` is not a valid callable");
	                    
	                    $fork = call_user_func($fork);
	                }
	                if(is_numeric($fork))
	                    $fork = $fork > 0;
	                else if(is_string($fork))
	                    $fork = strlen(trim($fork));
	                else if(is_array($fork))
	                    $fork = count($fork);
	                else if(is_object($fork))
	                    $fork = true;
	                else if(!is_bool($fork))
	                    $fork = false;
	                return $fork ? $args[1] : $args[2];
	            
	            default:
	                if(!is_callable($call))
					    throw new Exception("$call is not a valid callable or virtual method");
	                return call_user_func_array($call, $args);
	        }
	    else if(!is_callable($call))
	        throw new Exception(json_encode($call) . " is not a valid callable");
	    return call_user_func_array($call, $args);
	}
	
	public static function evaluate($string, $suppressExceptions=true){
		try{
			if(preg_match("/^(\w+)::(\w+)\((.*)\)$/", $string, $matches))
				return self::callVirtualFunction(Array($matches[1], $matches[2]), self::parseFuncArgs($matches[3]));
			else if(preg_match("/^(\w+)\((.*)\)$/", $string, $matches))
			    return self::callVirtualFunction($matches[1], self::parseFuncArgs($matches[2]));
			else
				throw new Exception("Invalid Syntax");
		}catch(Exception $e){
			if($suppressExceptions)
				return Array("Message" => $e->getMessage(), "Original" => $string);
			throw $e;
		}
	}

}
?>
