<?php
abstract class ShippingProvider {

	public abstract function getName();
	public abstract function getImage();

	/*
		
	 $from/to = Array(CountryCode, PostalCode)
			
	 returns Array(Array(
	 "code" => (integer),
	 "name" => (string),
	 "cost" => (float)
	 ) ...)
			
	 */
	public abstract function getQuotes($from, $to, $packages);

}
?>
