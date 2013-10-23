<?php
class DomainEnvironment {

	private static $environment = false;

	protected static function loadEnv() {
		if (!self::$environment) {
			$db = Database::getInstance();

			self::$environment = Array();
			foreach ($db->select("environment", Array("domain" => "")) as $row) {
				self::$environment[$row['key']] = $row['value'];
			}

			foreach ($db->select("environment", Array("domain" => DOMAIN_SL, "exact" => 0)) as $row) {
				self::$environment[$row['key']] = $row['value'];
			}

			foreach ($db->select("environment", Array("domain" => DOMAIN, "exact" => 1)) as $row) {
				self::$environment[$row['key']] = $row['value'];
			}
		}
	}

	public static function getEnvironment() {
		self::loadEnv();
		return self::$environment;

	}

	public static function resolve($key) {
		self::loadEnv();
		return self::$environment[$key];
	}

}
?>
