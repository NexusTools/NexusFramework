<?php
class SystemInfo {

	const LOAD_AVERAGE_MINUTE = 0;
	const LOAD_AVERAGE_FIVE_MINUTES = 1;
	const LOAD_AVERAGE_FIFTEEN_MINUTES = 2;

	const FORMAT_RAW = 0;
	const FORMAT_BITS = 1;
	const FORMAT_BYTES = 2;
	const FORMAT_PERCENTAGE = 3;

	private static $loadColors = array(array(127, 127, 127), array(232, 185, 0),
		array(244, 0, 0), array(150, 30, 190));

	private static $memory = false;
	private static $loadavg = false;

	protected static function format($val, $type) {
		switch ($type) {
		case self::FORMAT_PERCENTAGE:
			return (round($val * 1000) / 10)."%";

		case self::FORMAT_BITS:
		case self::FORMAT_BYTES:
			$bits = $type == self::FORMAT_BITS;
			return StringFormat::formatFilesize($val * 1024, $bits);

		default:
			return $val;

		}
	}

	public static function getHostName() {
		return gethostname();
	}

	public static function getMemoryInfo() {
		if (!self::$memory) {
			$handle = popen('free', 'r');
			fgets($handle); // Skip Headers
			preg_match("/^Mem\:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", fgets($handle), $memory);
			fgets($handle); // Skip Other
			preg_match("/^Swap\:\s+(\d+)\s+(\d+)\s+(\d+)/", fgets($handle), $swap);
			pclose($handle);
			self::$memory = array($memory, $swap);
		}
		return self::$memory;
	}

	public static function getLoadAverages() {
		if (!self::$loadavg) {
			preg_match("/^(\d+\.\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)/",
				file_get_contents("/proc/loadavg"), self::$loadavg);
			array_shift(self::$loadavg);
		}
		return self::$loadavg;
	}

	public static function fromColor($base, $percent) {
		$mod = array();
		$to = self::$loadColors[$base + 1];
		$from = self::$loadColors[$base];
		array_push($mod, round(($to[0] - $from[0]) * $percent));
		array_push($mod, round(($to[1] - $from[1]) * $percent));
		array_push($mod, round(($to[2] - $from[2]) * $percent));
		return array($from[0] + $mod[0], $from[1] + $mod[1], $from[2] + $mod[2]);
	}

	// Normally dark colors
	public static function getHTMLLoad($time = self::LOAD_AVERAGE_MINUTE, $lighten = false) {
		$load = self::getLoad($time);
		if ($load >= 1) {
			if ($load >= 5)
				$color = self::$loadColors[3];
			else
				if (load >= 3)
					$color = self::fromColor(2, ($load - 3) / 2);
				else
					$color = self::fromColor(1, ($load - 1) / 2);
		} else
			$color = self::fromColor(0, $load / 1);

		$code = "#";
		foreach ($color as $c) {
			if ($lighten)
				$c *= 1.5;
			if ($c > 255)
				$c = 255;
			if ($c < 0)
				$c = 0;
			$c = dechex($c);
			if (strlen($c) < 2)
				$code .= "0$c";
			else
				$code .= $c;
		}

		return "<font color=\"$code\">$load</font>";
	}

	public static function getLoad($time = self::LOAD_AVERAGE_MINUTE) {
		$loadavg = self::getLoadAverages();
		return $loadavg[$time ? $time : 0];
	}

	public static function getRamUsagePercent() {
		$mem = self::getMemoryInfo();
		return self::format(($mem[0][2] - $mem[0][6]) / $mem[0][1], self::FORMAT_PERCENTAGE);
	}

	public static function getRamUsage($format = self::FORMAT_RAW) {
		$mem = self::getMemoryInfo();
		return self::format($mem[0][2] - $mem[0][6], $format);
	}

	public static function getRamTotal($format = self::FORMAT_RAW) {
		$mem = self::getMemoryInfo();
		return self::format($mem[0][1], $format);
	}

	public static function getSwapUsagePercent() {
		$mem = self::getMemoryInfo();
		return self::format($mem[1][2] / $mem[1][1], self::FORMAT_PERCENTAGE);
	}

	public static function getSwapUsage($format = self::FORMAT_RAW) {
		$mem = self::getMemoryInfo();
		return self::format($mem[1][2], $format);
	}

	public static function getSwapTotal($format = self::FORMAT_RAW) {
		$mem = self::getMemoryInfo();
		return self::format($mem[1][1], $format);
	}

}
?>
