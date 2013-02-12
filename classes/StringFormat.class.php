<?php
class StringFormat {

	private static $keywords = Array(
							"mysql" => "MySQL",
							"sqlite" => "SQLite",
							"html" => "HTML",
							"nexustools" => "NexusTools"
									);
									
	public static function formatTimeUntil($thanSecs){
		return self::formatTimeLeft($thanSecs - time());
	}
	
	public static function stringForBoolean($bool){
	    return $bool ? "Yes" : "No";
	}
	
	public static function cleanPhoneNumber($input){
		return self::expandPhoneNumber(self::condensePhoneNumber($input));
	}
	
	public static function expandPhoneNumber($input){
		$input = trim($input);
		if(preg_match("/^(\+\d)?(\d\d\d)(\d\d\d)(\d\d\d\d)(x\d+)?$/", $input, $match)) {
			$out = "";
			if($match[1])
				$out .= $match[1] . " ";
			
			$out .= "($match[2]) $match[3] $match[4]";
			if($match[5])
				$out .= " ext " . substr($match[5], 1);
			echo $out;
		} else
			return $input;
	}
	
	public static function condensePhoneNumber($input){
		$input = trim($input);
		if(preg_match("/^(\d[\-\s\.]|\+\d)?[\-\s\.]*\(?(\d\d\d)\)?[\-\s\.]*(\d\d\d)[\-\s\.]*(\d\d\d\d)([\-\s]*(e(xt(ension)?)?|x|\+)[\-\s]*(\d+))?$/", $input, $match)) {
		
			$out = "";
			if($match[1]){
				if(startsWith($match[1], "+"))
					$out .= $match[1];
				else
					$out .= "+" . intval($match[1]);
			}
			$out .= $match[2];
			$out .= $match[3];
			$out .= $match[4];
		
			if(($end = count($match)) > 5) {
				$out .= "x";
				$out .= intval($match[$end-1]);
			}
		
			return $out;
		} else 
			return $input;
	}
	
	public static function properCase($string){
		return preg_replace_callback("/[\w\.]+/", Array(__CLASS__, "caseWord"), $string);
	}
	
	public static function caseWord($word){
		$word = $word[0];
		
		if(strlen($word) > 2 && $word != "and")
			return ucfirst($word);
		else
			return strtolower($word);
	}
	
	public static function formatTimeLeft($secs){
		if($secs <= 1 && $secs >= -1)
			return "Now";
	
		if($secs < 0)
			return self::formatTimeLeft(-$secs) . " Ago";
		
		$unit = "Second";
		if($secs >= 60){
			$secs /= 60;
			$unit = "Minute";
			if($secs >= 60){
				$secs /= 60;
				$unit = "Hour";
				if($secs >= 24){
					$secs /= 24;
					$unit = "Day";
					if($secs >= 7){
						$secs /= 7;
						$unit = "Week";
						if($secs >= 4){
							$secs /= 4;
							$unit = "Month";
							if($secs >= 12){
								$secs /= 12;
								$unit = "Year";
							}
						}
					}
				}
			}
		}
		
		$secs = ceil($secs);
		if($secs > 0)
			$unit .= "s";
		
		return "$secs $unit";
	}
									
	public static function __pregCallback($matches){
		return self::displayForKeyword($matches[0]);
	}
	
	public static function formatFilesize($size, $bits=false){
		$suffix = "B";
	
		if($bits){
			if($size >= 1000) {
				$suffix = "KiB";
				$size /= 1000;
			}
			if($size >= 1000) {
				$suffix = "MiB";
				$size /= 1000;
			}
			if($size >= 1000) {
				$suffix = "GiB";
				$size /= 1000;
			}
			if($size >= 1000) {
				$suffix = "TiB";
				$size /= 1000;
			}
		} else {
			if($size >= 1024) {
				$suffix = "KB";
				$size /= 1024;
			}
			if($size >= 1024) {
				$suffix = "MB";
				$size /= 1024;
			}
			if($size >= 1024) {
				$suffix = "GB";
				$size /= 1024;
			}
			if($size >= 1024) {
				$suffix = "TB";
				$size /= 1024;
			}
		}
		
		return (round($size * 100) / 100) . $suffix;
	}
	
	public static function idForDisplay($display){
		$display = str_replace(" ", "-", $display);
		return  str_replace("--", "-", strtolower(preg_replace("/[^\w\-\d]/", "", $display)));
	}
	
	public static function formatCondition($condition){
		if(strlen($condition)) {
			$condition = str_replace("==", "Is", $condition);
			$condition = str_replace("&&", "And", $condition);
			$condition = str_replace("||", "Or", $condition);
			$condition = str_replace("!", "Not ", $condition);
			return "If $condition";
		} else
			return "Always";
	}
	
	public static function formatDateForTimestamp($stamp, $neverForZero=true){
		return self::formatDate(Database::timestampToTime($stamp), $neverForZero);
	}
	
	public static function formatDate($time=false, $neverForZero=true, $inputTZ="UTC"){
		return DateFormat::format($time, $neverForZero, $inputTZ);
	}

	public static function displayForKeyword($keyword){
		$keyword = strtolower($keyword);
		if(isset(self::$keywords[$keyword]))
			return self::$keywords[$keyword];
		return ucfirst($keyword);
	}

	public static function displayForID($id){
		if(preg_match("/^(\w+-)+\w+$/", $id)) // dash separated id
			$id = str_replace("-", " ", $id);
		if(preg_match("/^(\w+_)+\w+$/", $id)) // underscore separated id
			$id = str_replace("_", " ", $id);
		$id = preg_replace("/\d+/", " $0 ", $id);
		$id = preg_replace("/\s+/", " ", $id);
		$id = preg_replace_callback("/\w+/", Array(__CLASS__, "__pregCallback"), $id);
		return $id;
	}
	
	public static function formatPrice($price){
		return "$" . number_format((float)$price, 2);
	}
	
	public static function registerKeyword($keyword, $display){
		self::$keywords[$keyword] = $display;
	}

}
?>
