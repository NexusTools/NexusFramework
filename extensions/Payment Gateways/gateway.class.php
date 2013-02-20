<?php
abstract class PaymentGateway {

	private static $settings;
	private static $gateways = Array();

	public static function init() {
		self::$settings = new Settings("Payment Gateway");
	}
	
	public static function getCompanyName(){
		return self::$settings->getString("company-name", DOMAIN_SL);
	}
	
	public static function getConfirmURI(){
		if(self::$settings->isValidString("review-uri"))
			return self::$settings->getValue("review-uri");
		throw new Exception("No Review URI Set");
	}
	
	public static function getReviewURI(){
		if(self::$settings->isValidString("review-uri"))
			return self::$settings->getValue("review-uri");
		throw new Exception("No Review URI Set");
	}
	
	public static function getCancelURI(){
		return self::$settings->getString("cancel-uri", DOMAIN_SL);
	}
	
	public static function isTestingMode(){
		return self::$settings->getBoolean("testing", true);
	}

	public static function getGateways(){
		return self::$gateways;
	}
	
	public static function getGateway($id){
		return self::$gateways[$id];
	}
	
	public static function registerGateway($impl){
		$id = StringFormat::idForDisplay($impl->getName());
		if(array_key_exists($id, self::$gateways))
			throw new Exception("Gateway `$id` Already Registered");
			
		self::$gateways[$id] = $impl;
	}
	
	public abstract function getName();
	public abstract function getLogo();
	
	/*
		
		products = Array(Array(
					"name" => (string),
					"cost" => (float),
					[
						"quantity" => (integer),
						"url" => (string)
					]
				) ...)
	
	*/
	public abstract function startCheckout($products, $invoiceID, $currencyCode=false);
	public abstract function confirmCheckoutPayment(); 
	
	
	public abstract function handleCallback($page);

} PaymentGateway::init();
?>
