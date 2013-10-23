<?php
class UserRelation {

	const RELATION_MORTAL_ENEMY = -2;
	const RELATION_ENEMY = -1;
	const RELATION_NEUTRAL = 0;
	const RELATION_FRIEND = 1;
	const RELATION_BEST_FRIEND = 2;

	private $uid;
	private static $database = false;

	public static function getDatabase() {
		return self::$database === false ? (self::$database = Database::getInstance()) : self::$database;
	}

	public function __construct($user) {
		$this->uid = $user->getID();
	}

	public function getStanceTowards($uid, $mutual = false) {
		$uid = User::resolveUserID($uid);

		$stance = self::getDatabase()->selectField("relations", Array("user" => $this->uid, "relation" => $uid), "stance");
		if ($stance === false)
			$stance = self::RELATION_NEUTRAL;
		if ($mutual) {
			$theirStance = User::getStanceTowardsByID($uid, $this->uid);
			if ($theirStance == $stance)
				return $stance;
			if ($theirStance < 0 && $stance < 0)
				return self::RELATION_ENEMY;
			if ($theirStance > 0 && $stance > 0)
				return self::RELATION_FRIEND;
			return self::RELATION_NEUTRAL;
		} else
			return $stance;
	}

	public function setStanceTowards($uid, $stance) {
		return self::getDatabase()->insert("relations", Array("user" => $this->uid,
			"relation" => User::resolveUserID($uid), "stance" => $stance), true) !== false;
	}

	public function isFriendsWith($uid, $requiresMutual = false) {
		return $this->getStanceTowards($uid, $requiresMutual) > 0;

	}

	public function isBestFriendsWith($uid, $requiresMutual = false) {
		return $this->getStanceTowards($uid, $requiresMutual) > 1;
	}

	public function listFriends($requiresMutual = false) {
		$friends = Array();
		foreach (self::getDatabase()->selectFields("relations", "relation", Array("user" => $this->uid)) as $relation) {
			if ($this->isFriendsWith($relation))
				array_push($friends, $relation);
		}

		return $friends;

	}

	public function isEnemiesWith($uid, $requiresMutual = false) {
		return $this->getStanceTowards($uid, $requiresMutual) < 0;
	}

	public function isMortalEnemiesWith($uid, $requiresMutual = false) {
		return $this->getStanceTowards($uid, $requiresMutual) < -1;
	}

	public function listEnemies($requiresMutual = false) {
		$friends = Array();
		foreach (self::getDatabase()->selectFields("relations", "relation", Array("user" => $this->uid)) as $relation) {
			if ($this->isEnemiesWith($relation))
				array_push($friends, $relation);
		}

		return $friends;
	}

}
?>
