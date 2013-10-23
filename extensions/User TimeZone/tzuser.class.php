<?php
class TimeZoneUser {

	private $uid;
	private static $database = false;
	private static $defaultTZ = false;

	public static function getDatabase() {
		return self::$database === false ? (self::$database = Database::getInstance()) : self::$database;
	}

	public function __construct($user) {
		$this->uid = $user->getID();
	}

	public function getTimeZone() {
		$db = self::getDatabase();
		$tz = $db->selectField("timezone", array("user" => $this->uid), "timezone");
		if (!self::$defaultTZ)
			self::$defaultTZ = date_default_timezone_get();
		if (!$tz)
			$tz = self::$defaultTZ;
		return $tz;
	}

	public function setTimeZone($tz) {
		$db = self::getDatabase();
		return $db->upsert("timezone", array("timezone" => $tz), array("user" => $this->uid));
	}

}
?>
