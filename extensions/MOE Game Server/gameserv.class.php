<?php
class MOEGameServer {

	private static $database;
	private static $settings;
	private static $servercache = Array();

	private function __construct($id) {
	}

	public function _isValid() {
		return true;
	}

	public function _queryPlayers() {
		return 0;
	}

	public static function init() {
		self::$database = Database::getInstance();
		self::$settings = new Settings("MOE Game Server");
	}

	public static function resolveGameID($id) {
		if (is_numeric($id))
			$id = self::$database->selectField("servers", Array("rowid" => $id), "rowid");
		else
			$id = self::$database->selectField("servers", Array("name" => $id), "rowid");

		if ($id === false)
			return -1;
		else
			return $id;
	}

	public static function fetch($id) {
		if (!is_numeric($id))
			$id = self::resolveGameID($id);

		if (!isset(self::$servercache[$id]))
			self::$servercache[$id] = new MOEGameServer($id);

		return self::$servercache[$id];
	}

	public static function getInstance() {
		$servID = self::$settings->getValue("default-server");
		return $servID ? self::fetch($serveID) : self::fetch(-1);
	}

	public static function __callStatic($name, $arguments) {
		if (endsWith($name, "ById") || endsWith($name, "ByID"))
			return self::fetch(array_shift($arguments))->__call(substr($name, 0, strlen($name) - 4), $arguments);

		return self::getInstance()->__call($name, $arguments);
	}

	public function __call($name, $arguments) {
		$method = false;
		try {
			$method = new ReflectionMethod(__CLASS__, "_$name");
			if (!$method->isStatic()) {
				if (!$this->_isValid())
					return false;
				$thisObject = $this;
			}
		} catch (Exception $e) {
		}

		if ($method && $method->isPublic()) {
			if ($method->isStatic())
				return $method->invokeArgs(null, $arguments);
			else {
				if (!$this->_isValid())
					return null;

				return $method->invokeArgs($thisObject, $arguments);
			}
		} else
			throw new Exception("Call to undefined method User::$name()");
	}

}
MOEGameServer::init();
?>
