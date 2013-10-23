<?php
class UserCounter {

	private static $database;
	private static $updated = false;

	public static function getDatabase() {
		return self::$database ? self::$database : (self::$database = Database::getInstance());
	}

	public static function getUserPage($user = false) {
		if ($user)
			$user = User::fetch($user)->getID();
		else
			$user = User::getID();

		return self::getDatabase()->selectField("tracks", Array("user" => $user), "page", false, "expires DESC");
	}

	public static function getOnlineCount($distinct = true, $includeGuests = false) {
		if ($distinct) {
			if (!$includeGuests)
				return self::getDatabase()->countDistinct("tracks", "user", Array(
					"!= user" => GuestUser::instance()->getID()));
			return self::getDatabase()->countDistinct("tracks", "user");
		}

		if (!$includeGuests)
			return self::getDatabase()->countRows("tracks", Array("> user" => GuestUser::instance()->getID()));
		return self::getDatabase()->countRows("tracks");
	}

	public static function getMemberCount() {
		return self::getDatabase()->countDistinct("tracks", "user", Array("level" => 0, "> user" => 0));
	}

	public static function getGuestCount() {
		return self::getDatabase()->countRows("tracks", Array("user" => GuestUser::instance()->getID()));
	}

	public static function getStaffCount() {
		return self::getDatabase()->countDistinct("tracks", "user", Array("> level" => 1));
	}

	public static function getOnlineStaff() {
		return self::getDatabase()->selectDistinctValues("tracks", "user", Array("> level" => 1));
	}

	public static function getOnPageCount($includeGuests = false, $path = null) {
		if ($path == null)
			$path = REQUEST_URI;
	}

	public static function tick($path = null) {
		if (defined("ERROR_OCCURED") || self::$updated)
			return;

		if (PageModule::hasError())
			return self::update();

		if ($path == null)
			$path = REQUEST_URI;

		self::$updated = true;
		self::getDatabase()->upsert("tracks", Array(
			"page" => REQUEST_URI,
			"user" => User::getID(),
			"level" => User::getLevel(),
			"expires" => Database::timeToTimestamp(strtotime("+1 minutes"))
		), Array("client" => ClientInfo::getUniqueID()));
	}

	public static function clean() {
		self::update();
		self::getDatabase()->delete("tracks", "expires <= CURRENT_TIMESTAMP");
	}

	public static function update() {
		if (defined("ERROR_OCCURED") || self::$updated)
			return;

		self::$updated = true;
		if (self::getDatabase()->update("tracks", Array(
			"expires" => Database::timeToTimestamp(strtotime("+1 minutes"))
		), Array("client" => ClientInfo::getUniqueID())) === 0)
			self::getDatabase()->upsert("tracks", Array(
				"page" => "[unknown]",
				"user" => User::getID(),
				"level" => User::getLevel(),
				"expires" => Database::timeToTimestamp(strtotime("+1 minutes"))
			), Array("client" => ClientInfo::getUniqueID()));
	}

}
?>
