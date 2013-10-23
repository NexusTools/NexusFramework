<?php
class GravatarUser {

	private $email;
	private static $domain = false;
	private static $defaultPath = false;

	public function __construct($user) {
		$this->email = $user->getEmail();
	}

	public static function getDatabase() {
		return self::$database ? self::$database : (self::$database = Database::getInstance());
	}

	public static function getAvatarDefault($size = 128) {
		if (!self::$domain)
			self::$domain = PROTOCOL_SECURE ? "https://secure.gravatar.com/avatar/" : "https://secure.gravatar.com/avatar/";

		return self::$domain."?s=".$size;
	}

	public function getAvatar($size = 128) {
		if (!self::$domain)
			self::$domain = PROTOCOL_SECURE ? "https://secure.gravatar.com/avatar/" : "https://secure.gravatar.com/avatar/";

		return self::$domain.md5(strtolower(trim($this->email)))."?s=".$size;
	}

}
?>
