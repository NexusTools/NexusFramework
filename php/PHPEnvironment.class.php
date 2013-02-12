<?php
class PHPEnvironment {

	private static $disabledFunctions = Array();
	private static $functionOverrides = Array();
	
	public static function init(){
		$disabled = explode(',', ini_get('disable_functions'));
		foreach ($disabled as $disableFunction)
		    self::$disabledFunctions[] = trim($disableFunction);
	}
	
	public static function setOverride($funcname, $override){
	}
	
	public static function isEnabled($funcname){
		return !in_array(self::$disabledFunctions, $funcname);
	}

} PHPEnvironment::init();
?>
