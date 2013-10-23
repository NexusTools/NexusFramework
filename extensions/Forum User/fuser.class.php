<?php
class ForumUser {

	private static $database;
	private $displayName;
	private $currentTitle;
	private $signature;
	private $location;
	private $aboutMe;
	private $user;

	public static function getDatabase() {
		return self::$database ? self::$database : (self::$database = Database::getInstance());
	}

	public function printForumUserCard($profileBase = "account") {
		echo "<a href='$profileBase/";
		echo $this->user->getUsername();
		echo "'><table class='user-card'><tr><td class='display-picture' rowspan=\"2\"><img src='";
		echo $this->user->getAvatar();
		echo "' /></td><td class='username' valign='bottom'>";
		echo $this->getDisplayName();
		echo "</td></tr><tr><td valign='top' class='title'>";
		echo $this->getTitle();
		echo "</td></tr></table></a>";
	}

	public function __construct($user) {
		$this->user = $user;
		$forumUser = self::getDatabase()->selectRow("account",
			Array("rowid" => $user->getID()));
		if (!$forumUser) {
			$forumUser = Array("rowid" => $this->user->getID(),
				"display-name" => $user->getUsername());
			self::getDatabase()->insert("account", $forumUser);
		}
		$this->displayName = $forumUser['display-name'];
		$this->currentTitle = $forumUser['custom-title'];
		$this->location = $forumUser['location'];
		$this->aboutMe = $forumUser['about-me'];
	}

	public function getAboutMe() {
		return $this->aboutMe;
	}

	public function getSignature() {
		return $this->signature;
	}

	public function getLocation() {
		return $this->location;
	}

	public function getTitle($long = false) {
		if (!$this->currentTitle) {
			if ($this->user->isStaff())
				$this->currentTitle = $long ? $this->user->getLongLevelString() : $this->user->getLevelString();
			else
				$this->currentTitle = self::getDatabase()->selectField("titles", Array("< req-rep" => $this->reputation), "title", "No Title", "req-rep DESC");
		}
		return $this->currentTitle;
	}

	public function getDisplayName() {
		return $this->displayName;
	}

	public function setDisplayName($display) {
		if (self::getDatabase()->update("account",
			Array("display-name" => $display),
			Array("rowid" => $this->user->getID()))) {
			$this->displayName = $display;
			return true;
		} else
			return false;
	}

}
?>
