<?php
class DateFormat {

    private static $timezones = Array();
    
    public static function getTimeZone($zoneid=false){
        if($zoneid === false)
            $zoneid = date_default_timezone_get();
        
        if(!array_key_exists($zoneid, self::$timezones))
            self::$timezones[$zoneid] = new DateTimeZone($zoneid);
        
        return self::$timezones[$zoneid];
    }
    
    public static function format($time, $neverForZero=true, $inputTZ="UTC", $format=DEFAULT_DATE_FORMAT) {
        if($time === false)
			$time = time();
		else if(is_numeric($time)) {
		    if($time == 0)
			    return "Never";
			
			$date = DateTime::createFromFormat("U", $time, self::getTimeZone($inputTZ));
	    } else
		    $date = new DateTime($time, self::getTimeZone($inputTZ));
		
		    
        $date->setTimeZone(self::getTimeZone());
        return $date->format($format);
    }
    
    public static function formatSqlTimestamp($time, $inputTZ="UTC") {
    	return self::format($time, false, $inputTZ, "m-d-Y H:i:s");
    }

}
?>
