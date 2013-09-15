<?php
class Logger {

	private static $db;
	
	const INFORMATION_LEVEL = 0;
	const WARNING_LEVEL = 1;
	const ERROR_LEVEL = 2;
	const CRITICAL_LEVEL = 3;
	
	public static function init() {
		self::$db = Database::getInstance();
	}

	public function log($level, $description, $details =null, $section =false, $subsection =false) {
		if($section === false)
			$section = "Unknown";
			
		$stack = null;
		try {
			$stack = serialize(debug_backtrace());
		}catch(Exception $e) {}
			
		$data = array("user" => User::getLoggedUserID(),
					"stack" => $stack,
					"level" => $level,
					"section" => $section,
					"subsection" => $subsection,
					"description" => $description,
					"address" => ClientInfo::getRemoteAddress(),
					"details" => is_string($details) ?
					json_encode($details) : null);
			
		self::$db->insert("logs", $data);
	}
	
	public function info($description, $details =false, $section =false, $subsection =false) {
		self::log(self::INFORMATION_LEVEL, $description, $details, $section, $subsection);
	}
	
	public function information($description, $details =false, $section =false, $subsection =false) {
		self::log(self::INFORMATION_LEVEL, $description, $details, $section, $subsection);
	}
	
	public function warn($description, $details =false, $section =false, $subsection =false) {
		self::log(self::WARNING_LEVEL, $description, $details, $section, $subsection);
	}
	
	public function warning($description, $details =false, $section =false, $subsection =false) {
		self::log(self::WARNING_LEVEL, $description, $details, $section, $subsection);
	}
	
	public function err($description, $details =false, $section =false, $subsection =false) {
		self::log(self::ERROR_LEVEL, $description, $details, $section, $subsection);
	}
	
	public function error($description, $details =false, $section =false, $subsection =false) {
		self::log(self::ERROR_LEVEL, $description, $details, $section, $subsection);
	}
	
	public function crit($description, $details =false, $section =false, $subsection =false) {
		self::log(self::CRITICAL_LEVEL, $description, $details, $section, $subsection);
	}
	
	public function critical($description, $details =false, $section =false, $subsection =false) {
		self::log(self::CRITICAL_LEVEL, $description, $details, $section, $subsection);
	}
	
	public function queryEntries($start =0, $limit =10, $orderBy ="created DESC") {
		return self::$db->queryRows("logs", false, $start, $limit, $orderBy);
	}

	public function trigger($module, $section, $subsection, $arguments){
		switch($module) {
			case "User":
				switch($section) {
					case "Timeout":
					case "Logout":
						self::info("{{User::getFullNameFor({user})}} - Session lasted {{TimeFormat::elapsed({duration})}}", $arguments, "User Session", $section);
						break;
						
					case "Login":
						self::info("{{User::getFullNameFor({user})}} from {{ClientInfo::htmlIPInfo({address})}}", $arguments, "User Session", $section);
						break;
				}
				break;
		}
	}

} Logger::init();
?>
