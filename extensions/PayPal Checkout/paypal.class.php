<?php
class PayPalExpressGateway extends PaymentGateway {

	private static $settings;
	private static $domain;

	public static function init() {
		self::$settings = new Settings("Payment Gateway", "Paypal Express");
		
		if(PaymentGateway::isTestingMode())
			self::$domain = "sandbox.paypal.com";
		else
			self::$domain = "paypal.com";
		
		if(self::$settings->hasValue("username") &&
			self::$settings->hasValue("password") &&
			self::$settings->hasValue("signature")) {
			if(PaymentGateway::isTestingMode() || PROTOCOL_SECURE)
				PaymentGateway::registerGateway(new self());
		}
		
	}

	public function getName(){
		return "PayPal";
	}
	
	public function getLogo() {
		return dirname(__FILE__) . DIRSEP . "paypal-logo.png";
	}
	
	protected static function callNVP($method, $arguments, $version=63){
		$arguments["METHOD"] = $method;
		$arguments["VERSION"] = 63;
		$arguments["USER"] = self::$settings->getValue("username");
		$arguments["PWD"] = self::$settings->getValue("password");
		$arguments["SIGNATURE"] = self::$settings->getValue("signature");
	
		$data = "";
		foreach($arguments as $key => $value) {
			if($data)
				$data .= "&";
		
			$data .= urlencode($key) . "=" . urlencode($value);
		}
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api-3t." . self::$domain . "/nvp");
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$httpResponse = curl_exec($ch);

		if(!$httpResponse)
		    throw new Exception("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		else {
			$responseData = Array();
			foreach(explode("&", $httpResponse) as $entry){
				$entry = explode("=", $entry);
				$responseData[$entry[0]] = urldecode($entry[1]);
			}
		
			if(array_key_exists("ACK", $responseData)) {
				$ack = $responseData['ACK'];
				switch($ack) {
					case "Success":
					case "SuccessWithWarning":
						break;
				
					case "Failure":
					case "FailureWithWarning":
					case "Warning":
						throw new Exception("NVP Error " . $responseData['L_ERRORCODE0'] . ": " . $responseData['L_LONGMESSAGE0']);
				
					default:
						throw new Exception("Invalid Response Code");
				}
			} else
				throw new Exception("Response Corrupt");
		
			return $responseData;
		}
	}
	
	protected static function paypalCommand($command, $arguments=Array()) {
		$url = "https://www." . self::$domain . "/webscr?cmd=" . urlencode($command);
		foreach($arguments as $key => $val)
			$url .= "&" . urlencode($key) . "=" . urlencode($val);
		Framework::redirect($url);
	}
	
	public function startCheckout($products, $invoiceID, $currencyCode=false) {
		$_SESSION['paypal-gateway'] = Array(); // Reset Paypal Session
	
		if(!count($products))
			throw new Exception("No Products in Cart");
	
		$args = Array();
		$args['NOSHIPPING'] = 1; // Shipping Not Implemented
		$args['ALLOWNOTE'] = 0; // Notes Not Implemented
		$args['RETURNURL'] = BASE_URL . "payment-gateway-callbacks/paypal/return";
		$args['CANCELURL'] = BASE_URL . "payment-gateway-callbacks/paypal/cancel";
		
		$args['BRANDNAME'] = PaymentGateway::getCompanyName();
		if($currencyCode)
			$args['PAYMENTREQUEST_0_CURRENCYCODE'] = $currencyCode;
		
		$prodID = 0;
		$totalCost = 0;
		foreach($products as $product) {
			$args["L_PAYMENTREQUEST_0_NAME$prodID"] = $product['name'];
			$args["L_PAYMENTREQUEST_0_AMT$prodID"] = round($product['cost'],2);
			
			if(array_key_exists("quantity", $product)) {
				$args["L_PAYMENTREQUEST_0_QTY$prodID"] = $product['quantity'];
				$totalCost += $product['cost']*$product['quantity'];
			} else
				$totalCost += $product['cost'];
			$prodID++;
		}
		
		if($totalCost <= 0)
			throw new Exception("No Cost Invoice");
		
		$totalCost = round($totalCost,2);
		$args['PAYMENTREQUEST_0_ITEMAMT'] = $totalCost;
		$args['PAYMENTREQUEST_0_AMT'] = $totalCost;
		$args['PAYMENTREQUEST_0_INVNUM'] = $invoiceID;
		$args['PAYMENTREQUEST_0_NOTIFYURL'] = BASE_URL . "payment-gateway-callbacks/paypal/ipn";
		
		$_SESSION['paypal-gateway']['checkout-arguments'] = $args;
		$args['PAYMENTACTION'] = "Order";
		
		$retData = self::callNVP("SetExpressCheckout", $args);
		if($retData['TOKEN']) {
			$_SESSION['paypal-gateway']["token"] = $retData['TOKEN'];
			self::paypalCommand("_express-checkout", Array("token" => $retData['TOKEN']));
		} else
			throw new Exception("Token Missing from Response");
	}
	
	public function confirmCheckoutPayment() {
		if(!$_SESSION['paypal-gateway']["token"])
			throw new Exception("No Token");
	
		while(ob_get_level())
			ob_end_clean();
			
		$args = $_SESSION['paypal-gateway']['checkout-arguments'];
		$args["TOKEN"] = $_SESSION['paypal-gateway']["token"];
		$args["PAYERID"] = $_SESSION['paypal-gateway']["payerid"];
		print_r(self::callNVP("DoExpressCheckoutPayment", $args));
		
		die();
	}
	
	public function handleCallback($page) {
		if(!$_SESSION['paypal-gateway']["token"])
			throw new Exception("No Token");
		
		switch($page) {
			case "ipn":
				header("Content-Type: text/plain");
				while(ob_get_level())
					ob_end_clean();
					
				$outFile = INDEX_PATH . "ipn.out";
				echo $outFile;
				file_put_contents($outFile, print_r($_GET, true) . print_r($_POST, true)); 
				die();
		
			case "return":
				$checkoutData = self::callNVP("GetExpressCheckoutDetails", Array("TOKEN" => $_SESSION['paypal-gateway']["token"]));
				
				if(!array_key_exists("PAYERID", $checkoutData))
					throw new Exception("Missing PayerID");
					
				$_SESSION['paypal-gateway']['payerid'] = $checkoutData["PAYERID"];
				Framework::redirect(PaymentGateway::getReviewURI());
				return;
				
			case "cancel":
				Framework::redirect(PaymentGateway::getCancelURI());
				return;
		}
	}

}
?>
