<?php
class ShippingCore {

	private static $providers = Array();
	
	public static function registerProvider($provider) {
		if(!$provider instanceof ShippingProvider)
			throw new ShippingException("Invalid Provider Object");
	
		$code = StringFormat::idForDisplay($provider->getName());
		if(array_key_exists($code, self::$providers))
			throw new ShippingException("Provider Already Registered");
			
		self::$providers[$code] = $provider;
	}
	
	/*
	
		$from/to = Array(CountryCode, PostalCode)
		$packages = Array(Array(
							"width" => (float), // inches
							"height" => (float),
							"depth" => (float),
							"weight" => (float) // pounds, lbs
						), ...)
		
		returns Array("provider" => Provider->getQuotes(), ...)
		
	*/
	public static function getQuotes($from, $to, $packages){
		$quotes = Array();
		foreach(self::$providers as $key => $provider) {
			try {
				$quotes[$key] = Array("name" => $provider->getName(),
							"quotes" => $provider->getQuotes($from, $to, $packages));
			} catch(Exception $e) {
				$quotes[$key] = Array("error" => $e->getMessage());
			}
		}
		return $quotes;
	}
	
}
?>
