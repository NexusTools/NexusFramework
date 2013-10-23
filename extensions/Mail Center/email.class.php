<?php
class Email {

	private static $activeID;
	private $html = false;
	private $text = false;
	private $headers = Array();
	private $tracking = false;

	public function __construct($headers = Array()) {
		foreach ($headers as $key => $val)
			$this->setHeader($key, $val);
	}

	public function setHeader($key, $val) {
		$this->headers[strtolower($key)] = $val;
	}

	public function removeHeader($key) {
		unset($this->headers[strtolower($key)]);
	}

	public function setTo($to, $name = false) {
		$this->headers["to"] = $name ? "$name <$to>" : $to;
	}

	public function enableTracking($on = true) {
		$this->tracking = $on;
	}

	public function captureHTML($more) {
		$this->html .= $more;
	}

	public function startHTMLCapture() {
		$this->html = "";
		OutputHandlerStack::pushOutputHandler(Array($this, "captureHTML"));
	}

	public function captureText($more) {
		$this->text .= $more;
	}

	public function startTextCapture() {
		$this->text = "";
		OutputHandlerStack::pushOutputHandler(Array($this, "captureText"));
	}

	public function finishCapture() {
		OutputHandlerStack::popOutputHandler();
	}

	public function setReplyTo($to, $name = false) {
		$this->headers["reply-to"] = $name ? "$name <$to>" : $to;
	}

	public function setSubject($subject) {
		$this->headers["subject"] = $subject;
	}

	public function setFrom($to, $name = false) {
		$this->headers["from"] = $name ? "$name <$to>" : $to;
	}

	public function addAttachment($file) {

	}

	public static function getViewURL() {
		return BASE_URL."mail-center/view-email?".urlencode(base64_encode(self::$activeID));
	}

	public static function getOptOutURL() {
		return BASE_URL."mail-center/opt-out?".urlencode(base64_encode(self::$activeID));
	}

	public function setHTML($html) {
		$this->html = $html;
	}

	public function setText($text) {
		$this->text = $text;
	}

	public static function trackLink($url) {
		if (!preg_match("/^\w+://", $url, $matches))
			$url = BASE_URL.relativepath($url);
		return MailCenter::trackLink($url, self::$activeID);
	}

	public function send($headers = Array(), $interpolate_vars = Array()) {
		$html = $this->html;
		$text = $this->text;
		$headers = array_merge($this->headers, $headers);
		foreach ($headers as $key => $val)
			if (strtolower($key) != $key) {
				unset($headers[$key]);
				$headers[strtolower($key)] = $val;
			}
		if (!isset($headers['to']))
			throw new Exception("To Header Missing");
		if (!isset($headers["from"]))
			$headers["from"] = "DO NOT REPLY <no-reply@".MailCenter::getOutgoingDomain().">";

		if (!$html && !$text)
			throw new Exception("Missing Payload");

		if (!isset($headers['subject']))
			$headers['subject'] = "";
		$data = Array(
			"to" => $headers["to"],
			"subject" => $headers['subject'],
			"from" => $headers['from']);
		if (isset($headers['x-mailing-list'])) {
			$data['mailing-list'] = $headers['x-mailing-list'];
			unset($headers['x-mailing-list']);
		}
		if (isset($headers['x-campaign'])) {
			$data['campaign'] = $headers['x-campaign'];
			unset($headers['x-campaign']);
		}
		if (isset($headers['x-expires'])) {
			$data['expires'] = Database::timeToTimestamp($headers['x-expires']);
			if ($headers['x-expires'] > time()) {
				$headers['expires'] = MailCenter::formatDate($headers['x-expires']);
				$headers['expiry-date'] = MailCenter::formatDate($headers['x-expires']);
			}
			unset($headers['x-expires']);
		}
		self::$activeID = MailCenter::getDatabase()->insert("emails", $data);
		if (!self::$activeID)
			throw new Exception("Failed to Create Email Entry", 0, MailCenter::getDatabase()->lastException());
		$emailID = urlencode(base64_encode(self::$activeID));
		//if($html && !$text)
		//	$text = "To view this email visit this address:\n" . $interpolate_vars['EMAIL_VIEW_URL'] . (isset($interpolate_vars['OPT_OUT_URL']) ? "\n\nTo Opt-Out of this mailing list visit:\n" . $interpolate_vars['OPT_OUT_URL'] : "");

		if ($html) {
			if ($this->tracking)
				$tracking = "<img width=\"1\" height=\"1\" src=\"".BASE_URL."mail-center/track?$emailID\" />";
			else
				$tracking = "";

			$htmlcharset = "utf-8";
			$html = utf8_encode("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"><html><head><META http-equiv=\"Content-Type\" content=\"text/html; charset=$htmlcharset\"></head><body style=\"margin: 0px\">".interpolate($html, true, $interpolate_vars)."$tracking</body></html>");
		}

		if (!$text)
			$text = strip_tags($html);
		else
			$text = utf8_encode(str_replace("\r\n.\r\n", "\n. \n", interpolate($text, true, $interpolate_vars)));

		if ($text && !$html) {
			$payload = $text;
			$headers['content-type'] = "text/plain; charset=utf-8";
		} else {

			$payload = "";
			$headers['mime-version'] = "1.0";
			srand(time());
			$mixed_boundary = "--==".base64_encode(md5(rand()))."==";
			$related_boundary = "--==".base64_encode(md5(rand()))."==";
			$alternative_boundary = "--==".base64_encode(md5(rand()))."==";
			$headers['content-type'] = "multipart/mixed;\n\tboundary=\"$mixed_boundary\"";
			$payload .= "--$mixed_boundary\r\nContent-Type: multipart/related;\n\tboundary=\"$related_boundary\"\r\n\r\n";
			$payload .= "--$related_boundary\r\nContent-Type: multipart/alternative;\n\tboundary=\"$alternative_boundary\"\r\n\r\n";

			$payload .= "--$alternative_boundary\r\nContent-Type: text/plain; charset=utf-8";
			$payload .= "\r\n\r\n";
			$payload .= $text;
			$payload .= "\r\n--$alternative_boundary\r\nContent-Type: text/html; charset=$htmlcharset";
			$payload .= "\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n";
			$payload .= quoted_printable_encode($html);
			$payload .= "\r\n--$alternative_boundary--\r\n\r\n";
			$payload .= "--$related_boundary--\r\n\r\n";
			$payload .= "--$mixed_boundary--\r\n";
		}

		$rawheaders = "";
		foreach ($headers as $key => $val) {
			if ($key == "subject" || $key == "to")
				continue;
			if (strlen($rawheaders))
				$rawheaders .= "\r\n";
			$rawheaders .= StringFormat::properCase($key).": $val";
		}

		$emailDir = MailCenter::getStoragePathForEmail(self::$activeID);
		file_put_contents($emailDir."payload.raw", "To: $headers[to]\r\nSubject: $headers[subject]\r\nMessage-Id: ".self::$activeID."\r\nDate: ".MailCenter::formatDate()."\r\n".$rawheaders."\r\n\r\n".$payload);
		file_put_contents($emailDir."payload.txt", $text);
		if ($html)
			file_put_contents($emailDir."payload.html", $html);
		return mail($headers['to'], $headers['subject'], $payload, $rawheaders);
	}

}
?>
