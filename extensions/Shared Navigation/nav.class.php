<?php
class Navigation {

	private static $database;

	public static function init() {
		self::$database = Database::getInstance();
	}

	public static function getDatabase() {
		return self::$database;
	}

	public static function invalidateMenu($provider) {
		self::$database->update("navigation", Array("exists" => false), Array("provider" => $provider));
	}

	public static function registerEntries($entries, $provider, $parent) {
		foreach ($entries as $entry) {
			if (isset($entry['title']))
				$text = $entry['title'];
			else
				$text = isset($entry['text']) ? $entry['text'] : $entry['name'];

			$rowid = self::$database->selectField("navigation", Array("provider" => $provider, "text" => $text, "parent" => $parent), "rowid");
			if (!$rowid)
				$rowid = self::$database->insert("navigation", Array("path" => $entry['path'], "condition" => $entry['condition'], "provider" => $provider, "text" => $text, "parent" => $parent, "exists" => true));
			else
				self::$database->update("navigation", Array("path" => $entry['path'], "condition" => $entry['condition'], "exists" => true), Array('rowid' => $rowid));

			if (isset($entry['children']) && $entry['children'])
				self::registerEntries($entry['children'], $provider, $rowid);
		}
	}

	public static function registerMenu($entry, $provider) {
		self::registerEntries(Array($entry), $provider, 0);
	}

	public static function getNavigationArray() {
		return self::$database->selectRecursive("navigation", 0, true, Array("exists" => true));
	}

}
Navigation::init();
?>
