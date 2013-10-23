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

	public static function log($level, $description, $details = null, $section = false, $subsection = false) {
		if ($section === false)
			$section = "Unknown";

		$stack = null;
		try {
			$stack = serialize(debug_backtrace());
		} catch (Exception $e) {
		}

		$data = array("user" => User::getLoggedUserID(),
			"stack" => $stack,
			"level" => $level,
			"section" => $section,
			"subsection" => $subsection,
			"description" => $description,
			"address" => ClientInfo::getRemoteAddress(),
			"details" => $details ? json_encode($details) : null);

		self::$db->insert("logs", $data);
	}

	public static function info($description, $details = false, $section = false, $subsection = false) {
		self::log(self::INFORMATION_LEVEL, $description, $details, $section, $subsection);
	}

	public static function information($description, $details = false, $section = false, $subsection = false) {
		self::log(self::INFORMATION_LEVEL, $description, $details, $section, $subsection);
	}

	public static function warn($description, $details = false, $section = false, $subsection = false) {
		self::log(self::WARNING_LEVEL, $description, $details, $section, $subsection);
	}

	public static function warning($description, $details = false, $section = false, $subsection = false) {
		self::log(self::WARNING_LEVEL, $description, $details, $section, $subsection);
	}

	public static function err($description, $details = false, $section = false, $subsection = false) {
		self::log(self::ERROR_LEVEL, $description, $details, $section, $subsection);
	}

	public static function error($description, $details = false, $section = false, $subsection = false) {
		self::log(self::ERROR_LEVEL, $description, $details, $section, $subsection);
	}

	public static function crit($description, $details = false, $section = false, $subsection = false) {
		self::log(self::CRITICAL_LEVEL, $description, $details, $section, $subsection);
	}

	public static function critical($description, $details = false, $section = false, $subsection = false) {
		self::log(self::CRITICAL_LEVEL, $description, $details, $section, $subsection);
	}

	public static function queryEntries($start = 0, $limit = 5, $orderBy = "created DESC") {
		return self::$db->queryRows("logs", array("user" => User::getLoggedUserID()), $start, $limit, $orderBy);
	}

	public static function trigger($module, $section, $arguments) {
		switch ($module) {
		case "User":
			switch ($section) {
			case "Timeout":
			case "Logout":
				self::info("{{User::getFullnameByID({user})}} - Session lasted {{TimeFormat::elapsed({duration})}}", $arguments, "User Session", $section);
				break;

			case "Login":
				self::info("{{User::getFullnameByID({user})}} from {{ClientInfo::htmlAddressInfo({address})}}", $arguments, "User Session", $section);
				break;
			}
			break;
		}
	}

}
Logger::init();
?>
