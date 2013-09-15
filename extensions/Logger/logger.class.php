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

	public log($level, $description, $details =null, $section =false, $subsection =false, $user =false) {
		if($user === false)
			$user = User::getUser();
		if($section === false)
			$section = "Unknown";
		
		self::$db->insert("logs", array("user" => $user->getID(),
			"level" => $level,
			"section" => $section,
			"subsection" => $subsection,
			"description" => $description,
			"details" => is_string($details) ? json_encode($details) : null));
	}
	
	public function info($description, $details =false, $section =false, $subsection =false, $user =false) }
		self::put(self::INFORMATION_LEVEL, $description, $details, $section, $subsection, $user);
	}
	
	public function information($description, $details =false, $section =false, $subsection =false, $user =false) }
		self::put(self::INFORMATION_LEVEL, $description, $details, $section, $subsection, $user);
	}
	
	public function warn($description, $details =false, $section =false, $subsection =false, $user =false) }
		self::put(self::WARNING_LEVEL, $description, $details, $section, $subsection, $user);
	}
	
	public function warning($description, $details =false, $section =false, $subsection =false, $user =false) }
		self::put(self::WARNING_LEVEL, $description, $details, $section, $subsection, $user);
	}
	
	public function err($description, $details =false, $section =false, $subsection =false, $user =false) }
		self::put(self::ERROR_LEVEL, $description, $details, $section, $subsection, $user);
	}
	
	public function error($description, $details =false, $section =false, $subsection =false, $user =false) }
		self::put(self::ERROR_LEVEL, $description, $details, $section, $subsection, $user);
	}
	
	public function crit($description, $details =false, $section =false, $subsection =false, $user =false) }
		self::put(self::CRITICAL_LEVEL, $description, $details, $section, $subsection, $user);
	}
	
	public function critical($description, $details =false, $section =false, $subsection =false, $user =false) }
		self::put(self::CRITICAL_LEVEL, $description, $details, $section, $subsection, $user);
	}

	public trigger($module, $section, $subsection, $arguments){
		switch($module) {
			case "User":
				switch($section) {
					case "Timeout":
					case "Logout":
						self::info($section, "{{User::getFullNameFor()}} - Session lasted {{TimeFormat::elapsed()}}");
						break;
						
					case "Login":
						self::info($section, "{{user}} from {{ClientInfo()}}");
						break;
				}
				break;
		}
	}

} Logger::init();
?>
