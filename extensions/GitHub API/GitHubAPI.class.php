<?php
class GitHubAPI extends GitHubAPICachedObject {
	public static $instance;
	public $url = 'https://api.github.com';
	private $username;
	private $password;
	public $timeout = 120;
	private $status;
	private $rateLimit;
	private $rateLimitRemaining;
	private $rateLimitResetAt;

	public static function getInstance() {
		if (!self::$instance)
			self::$instance = new GitHubAPI();
		return self::$instance;
	}

	protected function __construct() {
		GitHubAPICachedObject::__construct("GitHub-API");
	}

	public function setBaseURL($newURL) {
		$this->url = $newURL;
	}

	public function setTimeout($newTimeout) {
		$this->timeout = $newTimeout;
	}

	public function getStatus() {
		return $this->getValue('status');
	}

	public function getRateLimit() {
		return $this->getValue('rateLimit');
	}

	public function getRateLimitRemaining() {
		return $this->getValue('rateLimitRemaining');
	}

	public function getRateLimitResetAt() {
		return $this->getValue('rateLimitResetAt');
	}

	public function authenticate($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}

	public function isAuthenticated() {
		return (isset($this->username) && isset($this->password));
	}

	public function unauthenticate() {
		unset($this->username);
		unset($this->password);
	}

	private function doRequest($turl) {
		$c = curl_init();

		if ($this->isAuthenticated()) {
			curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($c, CURLOPT_USERPWD, $this->username.':'.$this->password);
		}

		curl_setopt($c, CURLOPT_HEADER, true);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "NexusFramework-GitHubAPI-Extension");
		curl_setopt($c, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($c, CURLOPT_HTTPGET, true);
		curl_setopt($c, CURLOPT_URL, $turl);

		$response = curl_exec($c);
		curl_close($c);

		return $response;
	}

	public function request($suffixURL) {
		$nurl = $this->url.$suffixURL;
		$response = $this->doRequest($nurl);

		$atHeader = true;
		$content = array();
		foreach (explode("\r\n", $response) as $line) {
			if ($atHeader && $line == '') {
				$atHeader = false;
			} else
				if ($atHeader) {
					$line = explode(': ', $line);
					switch ($line[0]) {
					case 'Status':
						$this->status = intval(substr($line[1], 0, 3));
						break;
					case 'X-RateLimit-Limit':
						$this->rateLimit = intval($line[1]);
						break;
					case 'X-RateLimit-Remaining':
						$this->rateLimitRemaining = intval($line[1]);
						break;
					case 'X-RateLimit-Reset';
					$this->rateLimitResetAt = intval($line[1]);
					break;
				}
			} else {
				array_push($content, $line);
			}
	}
	$this->invalidate();
	return $this->buildArray(json_decode(implode("\n", $content), true));
}

public function buildArray($content) {
	$classType = false;
	if (array_key_exists("type", $content)) {
		$classType = $content["type"];
	} else
		if (array_key_exists("fork", $content) || array_key_exists("_links", $content)) {
			$classType = "Branch";
		} else
			if (array_key_exists("commit", $content)) {
				$classType = "Commit";
			}
	if ($classType) {
		$nkeys = array();
		$nvalues = array();
		foreach ($content as $key => $value) {
			array_push($nkeys, $key);
			if (is_array($value))
				$value = $this->buildArray($value);
			array_push($nvalues, $value);
		}
		return GitHubAPIClassConstructor::getInstance()->constructClass($classType, $nkeys, $nvalues);
	} else {
		$newArray = array();
		foreach ($content as $key => $value) {
			if (is_array($value))
				$value = $this->buildArray($value);
			$newArray[$key] = $value;
		}
		return $newArray;
	}
}

public function getID() {
	return Framework::uniqueHash($this->username);
}

public function update() {
	return array("status" => $this->status,
		"rateLimit" => $this->rateLimit,
		"rateLimitRemaining" => $this->rateLimitRemaining,
		"rateLimitResetAt" => $this->rateLimitResetAt);
}

protected function getLifetime() {
	return 60 * 60 * 60;
}
}
?>
