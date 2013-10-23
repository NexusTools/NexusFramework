<?php
class UserAvatar {

	private $uid;
	private static $avatarPath = false;
	private static $database = false;

	public function __construct($user) {
		$this->uid = $user->getID();
	}

	public static function getAvatarPath() {
		if (!self::$avatarPath)
			self::$avatarPath = MEDIA_PATH."user-avatars".DIRSEP;
		if (!file_exists(self::$avatarPath) && !mkdir(self::$avatarPath, 0777, true))
			throw new Exception("Unable to create User Avatars storage directory.");
		return self::$avatarPath;
	}

	public static function getDatabase() {
		return self::$database ? self::$database : (self::$database = Database::getInstance());
	}

	public static function getAvatarDefault($raw = false) {
		$path = dirname(__FILE__).DIRSEP."default.png";
		return $raw ? $path : Framework::getReferenceURI($path);
	}

	public function setAvatar($path) {
		if (!$path)
			self::getDatabase()->delete("avatars", Array("rowid" => $this->uid));
		else
			return self::getDatabase()->upsert("avatars", array("avatar" => $path), array("rowid" => $this->uid));

		return true; // Assume deletions work.
		}

	public function getAvatar($raw = false) {
		$avatar = self::getDatabase()->selectField("avatars", Array("rowid" => $this->uid), "avatar");
		if (!$avatar)
			$avatar = self::getAvatarDefault($raw);
		else
			if (!$raw && !preg_match("/^\w+:.+$/", $avatar))
				$avatar = Framework::getReferenceURI($avatar);
		return $avatar;
	}

}
?>
