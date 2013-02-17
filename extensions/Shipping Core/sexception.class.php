<?php
class ShippingException extends Exception {

	public function __construct($message) {
		Exception::__construct($message);
	}

}
?>
