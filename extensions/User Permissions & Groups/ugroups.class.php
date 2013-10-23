<?php
class UserGroups {

	private static $database = false;
	private static $cache = Array();
	private static $bgroups = Array();
	private $accid;
	private $superadmin;
	private $gcache = Array();

	public static function initialize() {
		self::$database = Database::getInstance();

		array_push(self::$bgroups, Array('id' => - 1, "name" => "Super Admin"));
		array_push(self::$bgroups, Array('id' => - 2, "name" => "Admin"));
		array_push(self::$bgroups, Array('id' => - 3, "name" => "Staff"));
	}

	public static function getDatabase() {
		return self::$database;
	}

	public function __construct(&$user) {
		$this->accid = $user->getID();
		$this->superadmin = $user->isSuperAdmin();
	}

	public static function getGroupNameForID($id) {
		return self::$database->selectField("groups", Array("rowid" => $id), "name");
	}

	public static function countMembers($id) {
		if (is_string($id))
			$id = self::resolveGroupID($id);

		return self::$database->count("members", Array("group" => $id));
	}

	public static function resolveGroupID($name) {
		$name = strtolower($name);
		if (!isset(self::$cache[$name]))
			self::$cache[$name] = self::$database->selectField("groups", Array("LOWER(`name`)" => strtolower($name)), "rowid");

		return self::$cache[$name];
	}

	public function groups() {
		return self::$database->selectFields("members", Array("user" => $this->accid), "group");
	}

	public function inGroup($gid) {
		if ($this->superadmin)
			return true; // Super Admin is in ALL groups.

		if (!is_numeric($gid))
			$gid = self::resolveGroupID($gid);

		if (!isset($this->gcache[$gid]))
			$this->gcache[$gid] = self::$database->countRows("members", Array("user" => $this->accid, "group" => $gid));

		return $this->gcache[$gid];
	}

	public static function countGroupMembers($gid) {
		if ($gid > 0) {
			$statement = self::$database->prepare("SELECT count(*) FROM `members` WHERE `group` = ?");
			if (!$statement) {
				print_r(self::$database->errorInfo());
				die();
			}
			$statement->bindParam(1, $gid);
			if ($statement->execute() && ($data = $statement->fetch(PDO::FETCH_NUM)))
				return $data[0];
		} else {
			switch ($gid) {
			case - 1:
				return User::count("level >= 1");
			case - 2:
				return User::count("level >= 2");
			case - 3:
				return User::count("level >= 3");
			}
		}

		return 0;
	}

	public static function queryGroups($where = "", $args = Array(), $start = 0, $limit = 10) {
		if (strlen($where) > 0)
			$ext = " WHERE $where";
		else
			$ext = "";

		if ($start < 3) {
			$builtin = (3 - $start);
			$limit -= $builtin;

		} else
			$builtin = 0;

		$statement = self::$database->prepare("SELECT count(*) FROM groups$ext");
		foreach ($args as $key => $value)
			$statement->bindParam($key, $value);

		if (!$statement->execute() || !($count = $statement->fetch(PDO::FETCH_NUM)))
			return Array("total" => 0, "results" => Array());
		$count = $count[0];

		$statement = self::$database->prepare("SELECT rowid as id, * FROM groups$ext LIMIT $start,$limit");
		foreach ($args as $key => $value)
			$statement->bindParam($key, $value);

		if ($statement->execute()) {
			$groups = Array("total" => $count + 1, "results" => $statement->fetchAll(PDO::FETCH_ASSOC));

			for ($id = (3 - $builtin); $id < 3; $id++) {
				array_unshift($groups['results'], self::$bgroups[$id]);
			}

			return $groups;
		} else
			return Array("total" => 0, "results" => Array());
	}

}

UserGroups::initialize();
?>
