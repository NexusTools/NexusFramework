<?php
abstract class PaymentGateway {

	private static $settings;

	public static function init() {
		self::$settings = new Settings("Payment Gateway");
	}
	
	public static function getCompanyName(){
		return self::$settings->getString("company-name", DOMAIN_SL);
	}
	
	public static function getConfirmURI(){
		if(self::$settings->isValidString("review-url"))
			return self::$settings->getValue("review-url");
		throw new Exception("No Review URL Set");
	}
	
	public static function getReviewURI(){
		if(self::$settings->isValidString("review-url"))
			return self::$settings->getValue("review-url");
		throw new Exception("No Review URL Set");
	}
	
	public static function getCancelURI(){
		return self::$settings->getString("cancel-url", DOMAIN_SL);
	}
	
	public static function isTestingMode(){
		return self::$settings->getString("testing", true);
	}

	public static function getGateways(){
	}
	
	public static function getGateway($id){
	}
	
	public static function registerGateway($impl){
		$id = StringFormat::idForDisplay($impl->getName());
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

}
?>
