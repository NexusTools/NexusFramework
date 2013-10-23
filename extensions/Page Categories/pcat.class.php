<?php
class PageCategories {

	private static $database;
	private static $settings;

	public static function init() {
		self::$database = Database::getInstance();
	}

	public static function getDatabase() {
		return self::$database;
	}

	public static function runCategoryWidgets($catid, $footer, $slot, $mode = VirtualPages::RENDER) {
		foreach (VirtualPages::fetchWidgets($catid, $slot, 0,
			$footer ? "catfoot" : "cathead") as $widget)
			VirtualPages::runWidget($widget['type'], $mode, unserialize($widget['config']), $widget['rowid']);

		if ($catid) {
			$cat = self::$database->selectRow("categories", Array("rowid" => $catid));

			if ($footer) {
				if ($cat['inherit-footer'])
					self::runCategoryWidgets($cat['parent'], $footer, $slot, $mode);
			} else {
				if ($cat['inherit-header'])
					self::runCategoryWidgets($cat['parent'], $footer, $slot, $mode);
			}
		}
	}

	public static function resolveCategoryID($name) {
		if (is_numeric($name))
			return $name;

		return self::$database->selectField("categories", Array("LOWER(`name`)" => strtolower($name)), "rowid");
	}

	public static function fetchCategory($id) {
		$id = self::resolveCategoryID($id);

		return self::$database->selectRow("categories", Array("rowid" => $id));
	}

	public static function getCategories($visibleOnly = false) {
		if ($visibleOnly)
			return self::$database->selectRecursive("categories", 0, true, Array("published" => 1, "navbar" => 1));
		else
			return self::$database->selectRecursive("categories");
	}

	public static function getLayoutForCategory($id) {
		if ($id == 0)
			return self::getDefaultLayout();

		$cat = self::fetchCategory($id);
		if ($cat['layout'] >= 0)
			return $cat['layout'];
		else
			return self::getLayoutForCategory($cat['parent']);
	}

	public static function getPathForCategory($category) {
		$path = "";
		while (($entry = self::$database->selectRow("categories", Array("rowid" => $category), Array("parent", "name"))) !== false) {
			if ($category != 0)
				$path = "/".StringFormat::idForDisplay($entry['name']).$path;

			$category = $entry['parent'];
		}

		return $path;
	}

	public static function checkStatusRecursively($catid) {
		if ($catid == 0)
			return true;

		return self::checkStatusRecursively(self::$database->selectField("categories", Array("rowid" => $catid), "parent")) && eval("return ".self::$database->selectField("categories", Array("rowid" => $catid), "condition").";");
	}

	public static function setDefaultLayout($layout) {
		if (!self::$settings)
			self::$settings = new Settings("Page Categories");
		self::$settings->setValue("layout", $layout);
	}

	public static function getDefaultLayout() {
		if (!self::$settings)
			self::$settings = new Settings("Page Categories");

		return self::$settings->getValue("layout", 0);
	}

	public static function categoryNameForID($id) {
		return self::$database->selectField("categories", Array("rowid" => $id), "name");
	}

}
PageCategories::init();
?>
