<?php
class TimeFormat {

	public static function elapsed($time = false) {
		if (!is_numeric($time))
			$time = time();

		$timeStr = "";
		$mins = floor($time / 60);
		$hours = floor($mins / 60);
		$days = floor($hours / 24);
		$months = floor($days / 30);
		$time = $time % 60;
		$mins = $mins % 60;
		$hours = $hours % 24;
		$days = $days % 30;

		if ($months > 0) {
			$timeStr = "$months month";
			if ($months > 1)
				$timeStr .= "s";
		}
		if ($days > 0) {
			if (strlen($timeStr))
				$timeStr .= ", ";
			$timeStr .= "$days day";
			if ($days > 1)
				$timeStr .= "s";
		}
		if ($hours > 0) {
			if (strlen($timeStr))
				$timeStr .= ", ";
			$timeStr .= "$hours hour";
			if ($hours > 1)
				$timeStr .= "s";
		}
		if ($mins > 0) {
			if (strlen($timeStr))
				$timeStr .= ", ";
			$timeStr .= "$mins minute";
			if ($mins > 1)
				$timeStr .= "s";
		}
		if ($time > 0) {
			if (strlen($timeStr))
				$timeStr .= ", ";
			$timeStr .= "$time second";
			if ($time > 1)
				$timeStr .= "s";
		}

		if ($timeStr) {
			if (($pos = stripos($timeStr, ",")) !== - 1)
				$timeStr = substr($timeStr, 0, $pos)." and".substr($timeStr, $pos + 1);

			return $timeStr;
		}

		return "Now";
	}

}
?>
