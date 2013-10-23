<?php
class MailCenter {

	private static $storagePath;
	private static $database;
	private static $domain;
	public static function initialize() {
		self::$database = Database::getInstance();
		self::$storagePath = INDEX_PATH."config".DIRSEP."Mail Center".DIRSEP;
	}

	public static function trackEmailView($id) {
		if (User::isAdmin())
			return;
		$email = self::getEmail($id);
		self::$database->incrementField("emails", Array("rowid" => $id), "views");
		if ($email['campaign'])
			self::$database->incrementField("campaigns", Array("rowid" => $email['campaign']), "views");

	}

	public static function trackLink($url, $email) {
		$code = self::$database->selectFIeld("email-links", Array("url" => $url, "email" => $email), "rowid");
		if (!$code)
			$code = self::$database->insert("email-links", Array("url" => $url, "email" => $email));

		return BASE_URL."mail-center/link?".urlencode(base64_encode($code));
	}

	public static function membersStringForMailingList($id) {
		$total = self::$database->countRows("mailing-list-users", Array("list" => $id));
		$optout = self::$database->countRows("mailing-list-users", Array("list" => $id, "opt-out" => 1));

		return "$total".($optout ? ", $optout Opt'd Out" : "");
	}

	public static function followLink($id) {
		$link = self::$database->selectRow("email-links", Array("rowid" => $id));
		if (!User::isAdmin()) {
			$email = self::getEmail($link['email']);
			self::$database->incrementField("emails", Array("rowid" => $link['email']), "interactions");
			self::$database->incrementField("email-links", Array("rowid" => $email['rowid']), "interactions");
			if ($email['campaign']) {
				self::$database->incrementField("campaigns", Array("rowid" => $email['campaign']), "interactions");
				if (!self::$database->selectFIeld("campaign-links", Array("url" => $url, "campaign" => $email['campaign']), "rowid"))
					self::$database->insert("campaign-links", Array("url" => $url, "campaign" => $email['campaign']));
			}
		}
		Framework::redirect($link['url']);
	}

	public static function renderEmailHTML($email) {
		if (preg_match("/^([\w\d\s\.]+) <([a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+)>$/",
			$email, $matches))
			return "$matches[1]<br /><sup>$matches[2]</sup>";
		else
			return $email;
	}

	public static function getStoragePathForEmail($id, $create = true) {
		return self::getStoragePath("emails".DIRSEP.$id, $create);
	}

	public static function formatDate($time = false) {
		if ($time === false)
			$time = time();
		return date("D, j M Y H:i:s O (T)", $time);
	}

	public static function getEmail($id) {
		$email = self::$database->selectRow("emails", Array("rowid" => $id));
		$expireTime = Database::timestampToTime($email['expires']);
		if (User::isAdmin() || ($expireTime == 0 || $expireTime > time()))
			return $email;
		return false;
	}

	public static function getStoragePath($subpath, $create = true) {
		$path = cleanpath(self::$storagePath.$subpath.DIRSEP);
		if (!is_dir($path)) {
			if (!$create)
				return false;
			if (!mkdir($path, 0777, true))
				throw new Exception("Failed to Create MailCenter Storage Directory");
		}
		return $path;
	}

	public static function getDatabase() {
		return self::$database;
	}

	public static function getOutgoingDomain() {
		if (!self::$domain) {
			self::$domain = DOMAIN;
			if (startsWith(self::$domain, "www."))
				self::$domain = substr(self::$domain, 4);
		}
		return self::$domain;
	}

	public static function nameForMailingListID($id) {
		$id = self::resolveMailingListID($id);
		if ($id == 0)
			return "None";
		return self::$database->selectField("mailing-lists", Array("rowid" => $id), "name");
	}

	public static function createTemplate($name, $text, $html = false) {
		$id = self::$database->insert("templates", Array("name" => $name));
		if (!$id)
			return false;
		$basePath = self::getStoragePath("templates".DIRSEP.$id);
		file_put_contents($basePath."payload.html", $html);
		file_put_contents($basePath."payload.txt", $text);
		return $id;
	}

	public static function stringForEmailStatus($status) {
		switch ($status) {
		case 0:
			return "Opt'd Out";
		case 1:
			return "Undeliverable";
		case 2:
			return "Sent";
		case 3:
			return "Viewed";
		case 4:
			return "Interaction";

		}
	}

	public static function nameForCampaignID($id) {
		$id = self::resolveCampaignID($id);
		if ($id == 0)
			return "None";
		return self::$database->selectField("campaigns", Array("mailing-list" => $id), "name");
	}

	public static function nameForTemplateID($id) {
		return self::$database->selectField("templates", Array("rowid" => $id), "name");
	}

	public static function resolveCampaignID($id) {
		if (is_numeric($id))
			return $id;
		return self::$database->selectField("campaign", Array("LOWER name" => strtolower($id)), "rowid");
	}

	public static function resolveMailingListID($id) {
		if (is_numeric($id))
			return $id;
		return self::$database->selectField("mailing-lists", Array("LOWER name" => strtolower($id)), "rowid");
	}

	public static function countMembersForMailingList($id) {
		return self::$database->countRows("mailing-list-users", Array("list" => self::resolveMailingListID($id)));
	}

}
MailCenter::initialize();
?>
