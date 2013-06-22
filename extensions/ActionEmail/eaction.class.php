<?php
class ActionEmail {

	private static $database = false;
	
	public static function getDatabase() {
		return self::$database ? self::$database : (self::$database = Database::getInstance());
	}

	public static function request($body, $actionCallback, $params =Array(), $actionText ="Continue", $to =null, $subject ="Confirmation required", $from ="NexusTools", $expires ="+1 week") {
		if($to === null)
			$to = User::getInstance();
		if($to && !is_array($to)) {
			$to = User::fetch($to);
			if(!$to->isValid())
				throw new Exception("To user invalid");
			$to = Array($to->getEmail(), $to->getFullName());
		}
		
		$unique = Framework::uniqueHash(false, Framework::RawHash, true);
		$id = self::getDatabase()->insert("actions", Array("code" => $unique,
					"callback" => $actionCallback,
					"arguments" => serialize($params),
					"expires" => DateFormat::formatSqlTimestamp($expires)));
		if($id < 1)
			throw new Exception("Failed to create action token");
		$created = Database::timestampToTime(self::getDatabase()->selectField("actions", Array("rowid" => $id), "created"));
		
		return self::sendUrl($body, BASE_URL . "?continue&token=" . urlencode(base64_encode($unique)) . "&created=$created&id=$id", $subject, $actionText, $to, $from);
	}
	
	public static function __sessionCallback($url, $sessionKey, $sessionData) {
		$_SESSION[$sessionKey] = $sessionData;
		Framework::redirect($url);
	}
	
	public static function __handleToken() {
		$data = self::getDatabase()->selectRow("actions", Array("rowid" => $_GET['id']), Array("code", "created", "expires"));
		if(!$data)
			throw new Exception("Unknown token id");
		$expires = Database::timestampToTime($data['expires']);
		if($expires > time())  {
			self::getDatabase()->delete("actions", Array("rowid" => $_GET['id']));
			throw new Exception("Token expired");
		}
		if($data['code'] != base64_decode($_GET['token']))
			throw new Exception("Token code mismatch");
		if(Database::timestampToTime($data['created']) != $_GET['created'])
			throw new Exception("Token creation time mismatch");
			
		$data = self::getDatabase()->selectRow("actions", Array("rowid" => $_GET['id']), Array("callback", "arguments"));
		if(!$data)
			throw new Exception("Token callback data corrupt");
			
		$data['arguments'] = unserialize($data['arguments']);
		self::getDatabase()->delete("actions", Array("rowid" => $_GET['id']));
		call_user_func_array($data['callback'], $data['arguments']);
		die();
	}
	
	public static function sendUrlSessionToken($body, $redirectUrl, $sessionKey, $subject ="Check it Out", $actionText ="Check it Out", $to =null, $from ="NexusTools", $expires ="+1 week") {
		$extraData = Array();
		if(is_array($sessionKey)) {
			$extraData = $sessionKey;
			$sessionKey = array_shift($extraData);
		}
		$sessionData = $_SESSION[$sessionKey];
		if(!is_array($sessionData))
			$sessionData = Array();
		foreach($extraData as $data) { 
			if(!is_array($data))
				throw new Exception("All entries after first of sessionKey must be arrays.");
			$sessionData = array_merge($sessionData, $data);
		}
		
		unset($_SESSION[$sessionKey]);
		return self::request($body, "ActionEmail::__sessionCallback", Array($redirectUrl, $sessionKey, $sessionData), $actionText, $to, $subject, $from, $expires);
	}

	
	public static function sendUrl($body, $actionUrl, $subject ="Check it Out", $actionText ="Check it Out", $to =null, $from ="NexusTools") {
		if($to === null)
			$to = User::getInstance();
		if($to && !is_array($to)) {
			$to = User::fetch($to);
			if(!$to->isValid())
				throw new Exception("To user invalid");
			$to = Array($to->getEmail(), $to->getFullName());
		}
		
		$email = new Email();
		$email->setSubject($subject);
		$email->setTo($to[0], $to[1]);
		if($from)
			$email->setFrom("no-reply@nexustools.net", $from);
		$email->setReplyTo("help@nexustools.net", "NexusTools Help");
		
		$text = $subject . " - NexusTools";
		$text .= "\n" . str_repeat("-", 40);
		$text .= "\n\n" . strip_tags($body);
		$text .= "\n\n$actionText - $actionUrl";
		$email->setText($text);
		
		$html = file_get_contents(fullpath("email/head.htm"));
		$html .= "<h2>$subject</h2>";
		$html .= "<p> " . nl2br($body) . "</p>";
		$html .= "<a style=\"background-image:url(http://next.nexustools.net/media/email/action-button.jpg);display:block;width:124px;color:inherit;text-decoration:none;min-height:36px;font-size:16px;line-height:36px\" href=\"";
		$html .= htmlspecialchars($actionUrl);
		$html .= "\" target=\"_blank\">$actionText</a><br />";
		$html .= file_get_contents(fullpath("email/foot.htm"));
		$email->setHTML($html);
		
		return $email->send();
	}

}
?>
