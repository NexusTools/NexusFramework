<?php
class Profiler {

	private static $timers = Array();
	private static $activeTimers = Array("Framework" => START_TIME);
	
	public static function start($name){
		self::$activeTimers[$name] = microtime(true);
	}
	
	public static function finish($name){
		if(!isset(self::$activeTimers[$name]))
			return;
	
		$time = self::$activeTimers[$name];
		unset(self::$activeTimers[$name]);
		self::$timers[$name] = number_format((microtime(true) - $time)*1000, 2);
	}
	
	public static function getElapsed($name){
		if(isset(self::$timers[$name]))
			return self::$timers[$name];
		return -1;
	}
	
	public static function getTimers(){
		return self::$timers;
	}

}
?>
