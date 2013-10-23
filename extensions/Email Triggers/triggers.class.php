<?php
class EmailTriggers {

	private static $database;

	public static function getDatabase() {
		return self::$database;
	}

	public static function init() {
		self::$database = Database::getInstance();
	}

	public static function callback($provider, $event, $data) {
		// TODO: Implement
		}

}
EmailTriggers::init();
?>
