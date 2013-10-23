<?php
class AssetLibrary {

	private static $db;

	public static function init() {
		$db = Database::getInstance();
	}

}
AssetLibrary::init();
?>
