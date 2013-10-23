<?php
class PaymentCore {

	private static $database;
	private static $gateways;

	const CONFIGURE_MODE = 0x0;

	public static function registerGateway($name, $script, $logo = false) {
		if ($logo)
			self::$gateways[$name] = Array("script" => $script, "logo" => fullpath($logo));
		else
			self::$gateways[$name] = $script;
	}

	public static function isGatewayValid($gateway) {
		return isset(self::$gateways[$gateway]);
	}

	public static function getGatewayNames() {
		return array_keys(self::$gateways);
	}

	public static function getLogoForGateway($name) {
		if (is_array(self::$gateways[$name]))
			return self::$gateways[$name]['logo'];
		else
			return false;
	}

	public static function runGatewayScript($name, $mode, $parameters = Array()) {
		if (is_array(self::$gateways[$name]))
			$script = self::$gateways[$name]['script'];
		else
			$script = self::$gateways[$name];

		$parameters['mode'] = $mode;
		$include = new PHPInclude($script);
		$include->run(PHPInclude::CAPTURE_OUTPUT, $parameters);
		return $include->getOutput();
	}

	public static function initialize() {
		self::$database = Database::getInstance();
	}

	public static function getDatabase() {
		return self::$database;
	}

	public static function queryInventoryByCategory($cat, $start = 0, $limit = 10, $publishedOnly = true) {
		$where = Array();
		$where["category"] = self::resolveCategoryID($cat);
		if ($publishedOnly)
			$where['published'] = true;
		return self::$database->queryRows("inventory", $where, $start, $limit);
	}

	public static function CategoryHTML($name) {
		return interpolate(self::$database->selectField("categories", Array("rowid" => self::resolveCategoryID($name)), "html"));
	}

	public static function resolveCategoryID($name) {
		if (is_numeric($name))
			return $name;

		$parts = explode("/", $name);
		$parent = 0;
		foreach ($parts as $part) {
			$parent = self::$database->selectField("categories", Array("LOWER(`name`)" => strtolower($name), "parent" => $parent), "rowid");
			if ($parent === false)
				break;
		}

		return $parent;
	}

	public static function resolveInventoryID($name) {
		if (is_numeric($name))
			return $name;
		return self::$database->selectField("inventory", Array("LOWER(`name`)" => strtolower($name)), "rowid");
	}

	public static function inventoryEntryByID($id) {
		return self::$database->selectRow("inventory", Array("rowid" => $id));
	}

	public static function productNameByID($id) {
		return self::$database->selectField("inventory", Array("rowid" => $id), "name");
	}

	public static function categoryNameForID($id) {
		$id = $id * 1;
		if ($id == 0)
			return "None";

		$usedIds = Array($id);
		$nextId = 0;
		$categoryString = "";
		while (($row = self::$database->selectRow("categories", Array("rowid" => $id),
			Array("parent", "name"), false)) !== false) {
			$categoryString = $row[1].(strlen($categoryString) ? "/".$categoryString : "");
			$id = $row[0];
			if ($id == 0) // Parent is None
				break;

			if (in_array($id, $usedIds))
				return "ERROR: Hierarchy is Self Referencing";

			array_push($usedIds, $id);
		}

		return $categoryString ? $categoryString : "ERROR: Reference to Invalid Category";
	}

}
PaymentCore::initialize();
?>
