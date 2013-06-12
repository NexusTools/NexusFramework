<?php
class WildcardMatch {

	private static $instances;
	private $pattern;
	private $wildcard;

	protected function __construct($test, $wildcard) {
		$this->wildcard = $wildcard;
		$wildcard = preg_quote($wildcard);
		$this->pattern = "/^" . preg_replace_callback("/($wildcard|[^$wildcard]+)/",
							Array($this, "pregCallback"), $test) . "$/i";
	}
	
	private function pregCallback($matches) {
		if($matches[0] == $this->wildcard)
			return ".*";
		else
			return preg_quote($matches[0], '/');
	}
	
	public static function instance($test, $wildcard="*") {
		$id = Framework::uniqueHash($test . $wildcard);
	
		if(array_key_exists($wildcard, self::$instances))
			$instance = self::$instances[$id];
		else {
			$instance = new WildcardMatch($test, $wildcard);
			self::$instances[$id] = $instance;
		}
		
		return $instance;
	}
	
	public function exactMatch($subject){
		return preg_match($this->pattern, $subject) > 0;
	}

}
?>
