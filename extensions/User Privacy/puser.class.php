<?php
class PrivateUser {

	private $uid;
	private $cache;
	private static $database = false;

	public static function getDatabase() {
		return self::$database === false ? (self::$database = Database::getInstance()) : self::$database;
	}

	public function __construct($user) {
		$this->uid = $user->getID();
	}

	public function getPrivacyValue($key, $default = 0) {
		$key = StringFormat::idForDisplay($key);
		if (!array_key_exists($key, $cache))
			return ($this->cache[$key] = intval(self::getDatabase()->selectField("privacy", Array("user" => $this->uid, "key" => $key), "value", $default)));
		return $this->cache[$key];
	}

	public function setPrivacyValue($key, $value) {
		$key = StringFormat::idForDisplay($key);
		if (self::getDatabase()->upsert("privacy",
			Array("value" => $value),
			Array("user" => $this->uid, "key" => $key))) {
			$this->cache[$key] = $value;
			return true;
		}
		return false;
	}

}
?>
