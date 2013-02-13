<?php
class ShippingCore {

	private static $providers = Array();
	
	public static function registerProvider($code, $provider) {
		self::$providers[$code] = $provider;
	}
	
	public static function calculateShippingCosts($from, $to){
		if(!($from instanceof ShippingAddress) || !$from->isValid())
			throw new Exception("from must be a valid ShippingAddress object");
			
		if(!($to instanceof ShippingAddress) || !$to->isValid())
			throw new Exception("from must be a valid ShippingAddress object");
	}
	
}
?>
