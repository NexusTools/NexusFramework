<?php
class PayPalExpressGateway extends PaymentGateway {

	private static $settings;
	private static $domain;

	public static function init() {
		self::$settings = new Settings("Payment Gateways", "Paypal Express");

		if (PaymentGateway::isTestingMode())
			self::$domain = "sandbox.paypal.com";
		else
			self::$domain = "paypal.com";

		if (self::$settings->hasValue("username") && self::$settings->hasValue("password") && self::$settings->hasValue("signature")) {
			if (PaymentGateway::isTestingMode() || PROTOCOL_SECURE)
				PaymentGateway::registerGateway(new self());
		}

	}

	public function getName() {
		return "PayPal";
	}

	public function getLogo() {
		return dirname(__FILE__).DIRSEP."paypal-logo.png";
	}

	protected static function callNVP($method, $arguments, $version = 63) {
		$arguments["METHOD"] = $method;
		$arguments["VERSION"] = 63;
		$arguments["USER"] = self::$settings->getValue("username");
		$arguments["PWD"] = self::$settings->getValue("password");
		$arguments["SIGNATURE"] = self::$settings->getValue("signature");

		$data = "";
		foreach ($arguments as $key => $value) {
			if ($data)
				$data .= "&";

			$data .= urlencode($key)."=".urlencode($value);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api-3t.".self::$domain."/nvp");
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$httpResponse = curl_exec($ch);

		if (!$httpResponse)
			throw new Exception("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		else {
			$responseData = Array();
			foreach (explode("&", $httpResponse) as $entry) {
				$entry = explode("=", $entry);
				$responseData[$entry[0]] = urldecode($entry[1]);
			}

			if (array_key_exists("ACK", $responseData)) {
				$ack = $responseData['ACK'];
				switch ($ack) {
				case "Success":
				case "SuccessWithWarning":
					break;

				case "Failure":
				case "FailureWithWarning":
				case "Warning":
					throw new Exception("NVP Error ".$responseData['L_ERRORCODE0'].": ".$responseData['L_LONGMESSAGE0']);

				default:
					throw new Exception("Invalid Response Code");
				}
			} else
				throw new Exception("Response Corrupt");

			return $responseData;
		}
	}

	protected static function paypalCommand($command, $arguments = Array()) {
		$url = "https://www.".self::$domain."/webscr?cmd=".urlencode($command);
		foreach ($arguments as $key => $val)
			$url .= "&".urlencode($key)."=".urlencode($val);
		Framework::redirect($url);
	}

	public function startCheckout($products, $currencyCode = false) {
		$_SESSION['paypal-gateway'] = Array(); // Reset Paypal Session

		if (!count($products))
			throw new Exception("No Products in Cart");

		$args = Array();
		$args['NOSHIPPING'] = 1; // Shipping Not Implemented
		$args['ALLOWNOTE'] = 0; // Notes Not Implemented
		$args['RETURNURL'] = BASE_URL."payment-gateway-callbacks/paypal/return";
		$args['CANCELURL'] = BASE_URL."payment-gateway-callbacks/paypal/cancel";

		$args['BRANDNAME'] = PaymentGateway::getCompanyName();
		if ($currencyCode)
			$args['PAYMENTREQUEST_0_CURRENCYCODE'] = $currencyCode;

		$prodID = 0;
		$totalCost = 0;
		foreach ($products as $product) {
			$args["L_PAYMENTREQUEST_0_NAME$prodID"] = $product['name'];
			$args["L_PAYMENTREQUEST_0_AMT$prodID"] = round($product['cost'], 2);

			if (array_key_exists("quantity", $product)) {
				$args["L_PAYMENTREQUEST_0_QTY$prodID"] = $product['quantity'];
				$totalCost += $product['cost'] * $product['quantity'];
			} else
				$totalCost += $product['cost'];
			$prodID++;
		}

		if ($totalCost <= 0)
			throw new Exception("No Cost Invoice");

		$totalCost = round($totalCost, 2);
		$args['PAYMENTREQUEST_0_ITEMAMT'] = $totalCost;
		$args['PAYMENTREQUEST_0_AMT'] = $totalCost;
		$args['PAYMENTREQUEST_0_NOTIFYURL'] = BASE_URL."payment-gateway-callbacks/paypal/ipn";

		$_SESSION['paypal-gateway']['checkout-arguments'] = $args;
		$args['PAYMENTACTION'] = "Order";

		$retData = self::callNVP("SetExpressCheckout", $args);
		if ($retData['TOKEN']) {
			$_SESSION['paypal-gateway']["token"] = $retData['TOKEN'];
			self::paypalCommand("_express-checkout", Array("token" => $retData['TOKEN']));
		} else
			throw new Exception("Token Missing from Response");
	}

	public function confirmCheckoutPayment() {
		if (!$_SESSION['paypal-gateway']["token"])
			throw new Exception("No Token");

		$args = $_SESSION['paypal-gateway']['checkout-arguments'];
		$args["TOKEN"] = $_SESSION['paypal-gateway']["token"];
		$args["PAYERID"] = $_SESSION['paypal-gateway']["payerid"];

		$data = self::callNVP("DoExpressCheckoutPayment", $args);
		if ($data['PAYMENTINFO_0_ACK'] == "Success") {
			$txnid = array_key_exists("PAYMENTINFO_0_TRANSACTIONID", $data) ? $data['PAYMENTINFO_0_TRANSACTIONID'] : null;
			if (array_key_exists("PAYMENTINFO_0_PAYMENTSTATUS", $data))
				switch ($data['PAYMENTINFO_0_PAYMENTSTATUS']) {
				case "Pending":
					$status = PaymentGateway::STATUS_PENDING;
					break;

				case "Completed":
					$status = PaymentGateway::STATUS_SUCCESS;
					break;

				case "Failed":
				case "Expired":
				case "Denied":
				case "Voided":
					$status = PaymentGateway::STATUS_FAILED;
					break;

				default:
					throw new Exception("Unknown Payment Status: ".$data['PAYMENTINFO_0_PAYMENTSTATUS']."\n\n".json_encode($data));
				}
			else
				throw new Exception("Cannot Handle Response: ".json_encode($data));
			return Array($status, $txnid);
		} else
			throw new Exception("Payment Failed with code: ".$data['PAYMENTINFO_0_ERRORCODE']);
	}

	public function handleCallback($page) {
		switch ($page) {
		case "ipn":
			$req = "cmd=_notify-validate";
			foreach ($_POST as $key => $value)
				$req .= "&".urlencode($key)."=".urlencode($value);

			$ch = curl_init('https://www.'.self::$domain.'/cgi-bin/webscr');
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

			if (!($res = curl_exec($ch))) {
				curl_close($ch);
				throw new Exception("Failed to Verify IPN: ".curl_error($ch)."\n\n".json_encode($_POST));
			}
			curl_close($ch);

			if (strcmp($res, "VERIFIED") == 0) {
				switch ($_POST['payment_status']) {
				case "Pending":
					Triggers::broadcast("Payment", "StatusUpdate",
						Array(PaymentGateway::STATUS_PENDING,
						$_POST['txn_id']));
					return;

				case "Completed":
					Triggers::broadcast("Payment", "StatusUpdate",
						Array(PaymentGateway::STATUS_SUCCESS,
						$_POST['txn_id']));
					return;

				case "Failed":
				case "Expired":
				case "Denied":
				case "Voided":
					Triggers::broadcast("Payment", "StatusUpdate",
						Array(PaymentGateway::STATUS_FAILED,
						$_POST['txn_id']));
					return;

				default:
					throw new Exception("Unknown Status Received: ".json_encode($_POST));

				}
			} else
				if (strcmp($res, "INVALID") == 0)
					throw new Exception("Invalid IPN Received: ".json_encode($_POST));

		case "return":
			if (!$_SESSION['paypal-gateway']["token"])
				throw new Exception("No Token");

			$checkoutData = self::callNVP("GetExpressCheckoutDetails", Array("TOKEN" => $_SESSION['paypal-gateway']["token"]));

			if (!array_key_exists("PAYERID", $checkoutData))
				throw new Exception("Missing PayerID");

			$_SESSION['paypal-gateway']['payerid'] = $checkoutData["PAYERID"];
			Framework::redirect(PaymentGateway::getReviewURI());
			return;

		case "cancel":
			if (!$_SESSION['paypal-gateway']["token"])
				throw new Exception("No Token");

			Framework::redirect(PaymentGateway::getCancelURI());
			return;
		}
	}

}
?>
