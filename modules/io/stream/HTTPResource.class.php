<?php
class HTTPResource extends ResourceStream {

	public function __construct($url) {
		$url = Url::locate($url);
	}

}
?>
