<?php
class DataTables {

	private static $database;
	private static $tableStorage;

	public static function init() {
		self::$database = Database::getInstance();
		self::$tableStorage = INDEX_PATH."config".DIRSEP."Data Tables".DIRSEP;
		if (!is_dir(self::$tableStorage))
			mkdir(self::$tableStorage);
	}

	public static function getDatabase() {
		return self::$database;
	}

}
DataTables::init();
?>
