<?php
class Url {

	private $scheme;
	private $host;
	private $port;
	private $user;
	private $pass;
	private $path;
	private $query;
	private $fragment;

	public function __construct($string) {
		$parts = parse_url($string);
		if(!$parts)
			throw new InvalidArgumentException("Expected a valid Url");
		
		$this->scheme = array_key_exists("scheme", $parts) ? $parts['scheme'] : "";
		$this->host = array_key_exists("host", $parts) ? urldecode($parts['host']) : "";
		$this->port = array_key_exists("port", $parts) ? $parts['port'] : "";
		$this->user = array_key_exists("user", $parts) ? urlencode($parts['user']) : "";
		$this->pass = array_key_exists("pass", $parts) ? urlencode($parts['pass']) : "";
		$this->path = array_key_exists("path", $parts) ? $parts['path'] : "";
		$this->query = array_key_exists("query", $parts) ? $parts['query'] : "";
		$this->fragment = array_key_exists("fragment", $parts) ? urldecode($parts['fragment']) : "";
	}
	
	public function scheme() {
		return $this->scheme;
	}
	
	public function host() {
		return $this->host;
	}
	
	public function port($resolveSchemeDefault =false) {
		if($this->port)
			return $this->port;
		if($resolveSchemeDefault) {
			switch($this->scheme) {
				case "ftp":
					return 21;
					
				case "ssh":
					return 22;
					
				case "telnet":
					return 23;
					
				case "smtp":
					return 25;
					
				case "http":
					return 80;
					
				case "https":
					return 443;
			}
		}
		return -1;
	}
	
	public function user() {
		return $this->user;
	}
	
	public function pass() {
		return $this->pass;
	}
	
	public function path() {
		return $this->path;
	}
	
	public function query() {
		if(!is_array($this->query))
			$this->query = self::parseQuery($this->query);
		return $this->query;
	}
	
	public function fragment() {
		return $this->fragment;
	}
	
	public function queryValue($key) {
		if(!is_array($this->query))
			$this->query = self::parseQuery($query->query);
		return $this->query[$key];
	}
	
	public function setQueryValue($key, $val) {
		if(!is_array($this->query))
			$this->query = self::parseQuery($query->query);
		if($val == null)
			unset($this->query[$key]);
		else
			$this->query[$key] = $val;
	}
	
	public function setQuery($query) {
		if(!is_array($query))
			$query = self::parseQuery($query);
		$this->query = $query;
	}
	
	public function setScheme($scheme) {
		$this->scheme = $scheme;
	}
	
	public function setHost($host) {
		$this->host = $host;
	}
	
	public function setPort($port) {
		if(!is_numeric($port))
			throw new InvalidArgumentException("Expected a numeric value");
		$this->port = $port;
	}
	
	public function setUser($user) {
		$this->user = $user;
	}
	
	public function setPass($pass) {
		$this->pass = $pass;
	}
	
	public function setPath($path) {
		$this->path = $path;
	}
	
	public function setFragment($fragment) {
		$this->fragment = $fragment;
	}
	
	public static function parseQuery($string) {
		$query = Array();
		preg_match_all("/&?([^=&]+)=?([^&]*)/", $string, $matches);
		$count = count($matches[0]);
		for($i=0; $i<$count; $i++)
			$query[urldecode($matches[1][$i])] = urldecode($matches[2][$i]);
		return $query;
	}
	
	public static function queryToString($query) {
		if(!is_array($query))
			return "$query";
		
		$string = "";
		foreach($query as $key => $val) {
			if($string)
				$string .= "&";
			$string .= urlencode($key);
			if($val)
				$string .= "=" . urlencode($val);
		}
		return $string;
	}

	public function __toString() {
		return $this->toString(true);
	}
	
	public function toString($hideLogin =false) {
		$urlString = "";
		if($this->scheme) {
			$urlString .= $this->scheme;
			$urlString .= ":";
		}
		if($this->host) {
			$urlString .= "//";
			if($this->user) {
				if($hideLogin)
					$urlString .= "[UserLogin]@";
				else {
					$urlString .= urlencode($this->user);
					if($this->pass)
						$urlString .= ":" . urlencode($this->pass);
					$urlString .= "@";
				}
			}
			$urlString .= $this->host;
		}
		if($this->path)
			$urlString .= $this->path;
		if($this->query)
			$urlString .= "?" . self::queryToString($this->query);
		if($this->fragment)
			$urlString .= "#" . urlencode($this->fragment);
		
		return $urlString;
	}

}
?>
