<?php
class TimeFormat {
	
	public function elapsed($time =false) {
		if(!$time)
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
		
		if($months > 0)
			$timeStr = "$months months";
		if($days > 0) {
			if(strlen($timeStr))
				$timeStr .= ", ";
			$timeStr .= "$days days";
		}
		if($hours > 0) {
			if(strlen($timeStr))
				$timeStr .= ", ";
			$timeStr .= "$hours hours";
		}
		if($mins > 0) {
			if(strlen($timeStr))
				$timeStr .= ", ";
			$timeStr .= "$mins minutes";
		}
		if($time > 0) {
			if(strlen($timeStr))
				$timeStr .= ", ";
			$timeStr .= "$time seconds";
		}
		
		return $timeStr;
	}	
	
}
?>
