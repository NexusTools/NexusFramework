<?php
class DatabaseUser extends UserBackend {

	private static $database = false;

	public static function getDatabase() {
		return self::$database === false ? (self::$database = Database::getInstance()) : self::$database;
	}

	public static function getStaffIDs($minLevel = 1) {
		return self::getDatabase()->selectFields("account", "rowid", Array(">= level" => $minLevel));
	}

	protected function registerDateImpl() {
		return Database::timestampToTime(self::getDatabase()->selectField("account",
			Array("rowid" => $this->getID()), "created"));
	}

	protected function setLevelImpl($level) {
		return self::getDatabase()->update("account", Array("level" => $level), Array("rowid" => $this->getID()));
	}

	protected function setEmailImpl($email) {
		return self::getDatabase()->update("account", Array("email" => $email), Array("rowid" => $this->getID()));
	}

	protected function setUsernameImpl($username) {
		return self::getDatabase()->update("account", Array("username" => $username), Array("rowid" => $this->getID()));
	}

	public function setPassword($password) {
		return self::getDatabase()->update("account", Array("password" => md5($password, true)), Array("rowid" => $this->getID()));
	}

	public function checkPassword($password) {
		return self::getDatabase()->selectField("account", Array("rowid" => $this->getID(),
			"password" => md5($password, true)),
			"rowid") === $this->getID();
	}

	public static function getUserForID($id) {
		$userData = self::getDatabase()->selectRow("account", Array("rowid" => $id), Array("email", "username", "level"), false, false);
		return $userData === false ? false : new DatabaseUser($id, $userData);
		return false;
	}

	protected function __construct($id, $args) {
		UserInterface::__construct($id, $args[0], $args[1], $args[2]);
	}

	public static function resolveUserIDByUsername($identifier) {
		return self::getDatabase()->selectField("account", Array("username" => $identifier), "rowid");
	}

	public static function resolveUserIDByEmail($identifier) {
		return self::getDatabase()->selectField("account", Array("email" => $identifier), "rowid");
	}

	public static function register($user, $pass, $email, $requireVerification = true) {
		$id = self::getDatabase()->insert("account", Array("username" => $user,
			"password" => md5($pass, true),
			"email" => $email,
			"level" => $requireVerification ? -1 : 0));
		return $id === false ? null : $id;
	}

}
?>
