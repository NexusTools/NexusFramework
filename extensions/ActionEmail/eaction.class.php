<?php
class ActionEmail {

	private static $database = false;
	private static $settings = false;

	public static function getSettings() {
		return self::$settings ? self::$settings : (self::$settings = new Settings("ActionEmail"));
	}

	public static function getDatabase() {
		return self::$database ? self::$database : (self::$database = Database::getInstance());
	}

	public static function request($body, $actionCallback, $params = Array(), $actionText = "Continue", $to = null, $subject = "Confirmation required", $from = false, $expires = "+1 week", $singleUse = true) {
		if ($to === null)
			$to = User::getInstance();
		if ($to && !is_array($to)) {
			$to = User::fetch($to);
			if (!$to->isValid())
				throw new Exception("To user invalid");
			$to = Array($to->getEmail(), $to->getFullName());
		}

		$unique = Framework::uniqueHash(false, Framework::RawHash, true);
		$id = self::getDatabase()->insert("actions", Array("code" => $unique,
			"callback" => $actionCallback,
			"arguments" => serialize($params),
			"single-use" => $singleUse,
			"expires" => DateFormat::formatSqlTimestamp($expires)));
		if ($id < 1)
			throw new Exception("Failed to create action token");
		$created = Database::timestampToTime(self::getDatabase()->selectField("actions", Array("rowid" => $id), "created"));

		return self::sendUrl($body, BASE_URL."action-email/continue?token=".urlencode(base64_encode($unique))."&created=$created&id=$id", $subject, $actionText, $to, $from);
	}

	public static function __sessionCallback($url, $sessionKey, $sessionData) {
		$_SESSION[$sessionKey] = $sessionData;
		Framework::redirect($url);
	}

	public static function disableActive() {
		if (!array_key_exists('actionemail-activeid', $_SESSION))
			return;

		$aid = $_SESSION['actionemail-activeid'];
		self::getDatabase()->delete("actions", Array("rowid" => $aid));
		unset($_SESSION['actionemail-activeid']);
	}

	public static function cancelActive() {
		if (!array_key_exists('actionemail-activeid', $_SESSION))
			return;

		unset($_SESSION['actionemail-activeid']);
	}

	public static function __handleToken() {
		$data = self::getDatabase()->selectRow("actions", Array("rowid" => $_GET['id']), Array("code", "created", "expires"));
		if (!$data)
			throw new Exception("Unknown token id");
		$_SESSION['actionemail-activeid'] = $_GET['id'];
		$expires = Database::timestampToTime($data['expires']);
		if ($expires > time()) {
			self::disableActive();
			throw new Exception("Token expired");
		}
		if ($data['code'] != base64_decode($_GET['token']))
			throw new Exception("Token code mismatch");
		if (Database::timestampToTime($data['created']) != $_GET['created'])
			throw new Exception("Token creation time mismatch");

		$data = self::getDatabase()->selectRow("actions", Array("rowid" => $_GET['id']), Array("callback", "single-use", "arguments"));
		if (!$data)
			throw new Exception("Token callback data corrupt");

		$data['arguments'] = unserialize($data['arguments']);
		if ($data['single-use'])
			self::disableActive();
		call_user_func_array($data['callback'], $data['arguments']);
		die();
	}

	public static function sendUrlSessionToken($body, $redirectUrl, $sessionKey, $subject = "Check it Out", $actionText = "Check it Out", $to = null, $from = false, $expires = "+1 week", $singleUse = true) {
		$extraData = Array();
		if (is_array($sessionKey)) {
			$extraData = $sessionKey;
			$sessionKey = array_shift($extraData);
		}
		$sessionData = $_SESSION[$sessionKey];
		if (!is_array($sessionData))
			$sessionData = Array();
		foreach ($extraData as $data) {
			if (!is_array($data))
				throw new Exception("All entries after first of sessionKey must be arrays.");
			$sessionData = array_merge($sessionData, $data);
		}

		unset($_SESSION[$sessionKey]);
		return self::request($body, "ActionEmail::__sessionCallback", Array($redirectUrl, $sessionKey, $sessionData), $actionText, $to, $subject, $from, $expires, $singleUse);
	}

	public static function sendUrl($body, $actionUrl, $subject = "Check it Out", $actionText = "Check it Out", $to = null, $from = false) {
		if (!$to)
			$to = User::getInstance();
		if ($to instanceof UserInterface)
			$to = Array($to->getEmail(), $to->getFullName());
		else
			if (!is_array($to)) {
				$to = User::fetch($to);
				if (!$to->isValid())
					throw new Exception("To user invalid");
				$to = Array($to->getEmail(), $to->getFullName());
			}
		$siteName = self::getSettings()->getString("site-name", StringFormat::properCase(DOMAIN_SL));

		if (!$from)
			$from = Array(self::getSettings()->getString("from-address", "no-reply@".DOMAIN),
				self::getSettings()->getString("from-name", $siteName));
		else
			if ($from instanceof UserInterface)
				$from = Array($from->getEmail(), $from->getFullName());
			else
				if (!is_array($from)) {
					$from = User::fetch($from);
					if (!$from->isValid())
						throw new Exception("From user invalid");
					$from = Array($from->getEmail(), $from->getFullName());
				}

		$email = new Email();
		$email->setSubject($subject);
		$email->setTo($to[0], $to[1]);
		$email->setFrom($from[0], $from[1]);
		$email->setReplyTo(self::getSettings()->getString("reply-address", "help@".DOMAIN),
			self::getSettings()->getString("reply-name", "$siteName Help"));

		$text = $subject." - $siteName";
		$text .= "\n".str_repeat("-", 40);
		$text .= "\n\n".strip_tags($body);
		$text .= "\n\n$actionText - $actionUrl";
		$email->setText($text);

		$lnkStyle = self::getSettings()->getString("button-style");
		if (!$lnkStyle) {
			$actionBtn = fullpath("email/action-button.png");
			if (!is_file($actionBtn))
				$actionBtn = fullpath("email/action-button.jpg");
			if (!is_file($actionBtn))
				$actionBtn = fullpath("email/action-button.gif");

			$defStyle = "color:inherit;text-decoration:none;font-size: 16px";
			if (is_file($actionBtn) && is_readable($actionBtn))
				$defStyle .= ";background-image:url(".Framework::getReferenceURL($actionBtn).");display:block;width:124px;min-height:36px;line-height:36px";

			self::getSettings()->setValue("button-style", $lnkStyle = $defStyle);
		}
		$html = file_get_contents(fullpath("email/head.htm"));
		$html .= "<h2>$subject</h2>";
		$html .= "<p> ".nl2br($body)."</p>";
		$html .= "<br /><a style=\"";
		$html .= htmlspecialchars($lnkStyle);
		$html .= "\" href=\"";
		$html .= htmlspecialchars($actionUrl);
		$html .= "\" target=\"_blank\">$actionText</a>";
		$html .= file_get_contents(fullpath("email/foot.htm"));
		$email->setHTML($html);

		return $email->send();
	}

}
?>
