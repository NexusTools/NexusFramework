<?php
abstract class PaymentGateway {

	const STATUS_FAILED = -1;
	const STATUS_PENDING = 0;
	const STATUS_SUCCESS = 1;

	private static $settings;
	private static $gateways = Array();

	public static function init() {
		self::$settings = new Settings("Payment Gateway");
	}

	public static function getCompanyName() {
		return self::$settings->getString("company-name", DOMAIN_SL);
	}

	public static function getConfirmURI() {
		if (self::$settings->isValidString("review-uri"))
			return self::$settings->getValue("review-uri");
		throw new Exception("No Review URI Set");
	}

	public static function getReviewURI() {
		if (self::$settings->isValidString("review-uri"))
			return self::$settings->getValue("review-uri");
		throw new Exception("No Review URI Set");
	}

	public static function getCancelURI() {
		return self::$settings->getString("cancel-uri", DOMAIN_SL);
	}

	public static function isTestingMode() {
		return self::$settings->getBoolean("testing", true);
	}

	public static function getGateways() {
		return self::$gateways;
	}

	public static function getGateway($id) {
		if (!array_key_exists($id, self::$gateways))
			throw new Exception("Unknown Payment Gateway Requested: ".$id."\n\nAvailable gateways are: ".json_encode(self::$gateways));
		return self::$gateways[$id];
	}

	public static function registerGateway($impl) {
		$id = StringFormat::idForDisplay($impl->getName());
		if (array_key_exists($id, self::$gateways))
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
	public abstract function startCheckout($products, $currencyCode = false);

	/*
		
	 Returns Array((int)Status[, (string)TransactionID])
		
	 */
	public abstract function confirmCheckoutPayment();

	/*
		
	 Transaction Status Updates use Triggers, broadcast("Payment", "StatusUpdate", Array((int)Status, (string)TransactionID));
		
	 */
	public abstract function handleCallback($page);

}
PaymentGateway::init();
?>
