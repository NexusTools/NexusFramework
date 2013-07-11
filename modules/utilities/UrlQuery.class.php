<?php
class UrlQuery extends LazyMap {
	
	protected function resolveData() {
		if(!is_array($this->data))
			$this->data = Url::parseQuery($this->data);
	}
	
	public static function instance($url) {
		if($url instanceof self)
			return $url;
		return new UrlQuery($url);
	}
	
	public function __toString() {
		return Url::queryToString($this->data);
	}
	
	public function getQuery() {
		return $this->getData();
	}

}
?>
