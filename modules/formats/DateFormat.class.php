<?php
class DateFormat {

	private static $timezones = Array();

	public static function getFormat() {
		return DEFAULT_DATE_FORMAT;
	}

	public static function getTimeZone($zoneid = false) {
		if ($zoneid === false)
			$zoneid = date_default_timezone_get();

		if (!array_key_exists($zoneid, self::$timezones))
			self::$timezones[$zoneid] = new DateTimeZone($zoneid);

		return self::$timezones[$zoneid];
	}

	public static function format($time = false, $neverForZero = true, $inputTZ = "UTC", $format = DEFAULT_DATE_FORMAT, $outputFormat = false) {
		if($time instanceof DateTime) 
			$date = $time;
		else {
			if ($time === false)
				$time = time();
			
			if (is_numeric($time)) {
				if ($time == 0)
					return "Never";

				$date = DateTime::createFromFormat("U", $time, self::getTimeZone($inputTZ));
			} else
				$date = new DateTime($time, self::getTimeZone($inputTZ));
		}

		if(!$outputFormat)
			$outputFormat = self::getTimeZone();
		$date->setTimeZone($outputFormat);
		return $date->format($format);
	}
	
	public static function formatSqlTimestamp($time, $inputTZ = "UTC") {
		return self::format($time, false, $inputTZ, "m-d-Y H:i:s", "UTC");
	}

}

if (!defined("DEFAULT_DATE_FORMAT"))
	define("DEFAULT_DATE_FORMAT", "M jS, Y @ g:ia T");
?>
