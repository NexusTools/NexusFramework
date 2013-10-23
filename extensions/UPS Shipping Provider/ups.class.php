<?php
class UPSShippingProvider extends ShippingProvider {

	private static $settings;
	private static $services = Array(
		11 => "Standard",
		3 => "Ground",
		12 => "3 Day Select",
		2 => "2nd Day Air",
		59 => "2nd Day Air AM",
		13 => "Next Day Air Saver",
		1 => "Next Day Air",
		14 => "Next Day Air Early A.M.",
		7 => "Worldwide Express",
		54 => "Worldwide Express Plus",
		8 => "Worldwide Expedited",
		65 => "World Wide Saver"
	);

	public static function init() {
		self::$settings = new Settings("Shipping Core", "UPS Provider");
		if (self::$settings->isValidString("shipper-id") && self::$settings->isValidString("access-key") && self::$settings->isValidString("username") && self::$settings->isValidString("password"))
			ShippingCore::registerProvider(new self());
	}

	public function getName() {
		return "UPS";
	}
	public function getImage() {
		return null;
	}

	private function getAccessRequest() {
		return "<?xml version=\"1.0\" ?>
<AccessRequest xml:lang='en-US'>
  <AccessLicenseNumber>"
			.self::$settings->getValue("access-key")."</AccessLicenseNumber>
  <UserId>"
			.self::$settings->getValue("username")."</UserId>
  <Password>"
			.self::$settings->getValue("password")."</Password>
</AccessRequest>
";
	}

	public function getQuotes($from, $to, $packages) {
		$ch = curl_init();

		$request = $this->getAccessRequest();
		$request .= "<?xml version=\"1.0\" ?>
<RatingServiceSelectionRequest>
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Shop</RequestAction>
    <RequestOption>Shop</RequestOption>
  </Request>
  <Shipment>
    <Description>Shopping Rates</Description>
    <Shipper>
      <ShipperNumber>"
			.self::$settings->getValue("shipper-id")."</ShipperNumber>
      <Address>
        <AddressLine1></AddressLine1>
        <AddressLine2 />
        <AddressLine3 />
        <City></City>
        <StateProvinceCode></StateProvinceCode>
        <PostalCode>$from[1]</PostalCode>
        <CountryCode>$from[0]</CountryCode>
      </Address>
    </Shipper>
    <ShipTo>
      <CompanyName></CompanyName>
      <AttentionName></AttentionName>
      <PhoneNumber></PhoneNumber>
      <Address>
        <AddressLine1></AddressLine1>
        <AddressLine2 />
        <AddressLine3 />
        <City></City>
        <PostalCode>$to[1]</PostalCode>
        <CountryCode>$to[0]</CountryCode>
      </Address>
    </ShipTo>
    <ShipFrom>
      <CompanyName></CompanyName>
      <AttentionName></AttentionName>
      <PhoneNumber></PhoneNumber>
      <FaxNumber></FaxNumber>
      <Address>
        <AddressLine1></AddressLine1>
        <AddressLine2 />
        <AddressLine3 />
        <City></City>
        <StateProvinceCode></StateProvinceCode>
        <PostalCode>$from[1]</PostalCode>
        <CountryCode>$from[0]</CountryCode>
      </Address>
    </ShipFrom>";

		foreach ($packages as $package) {
			$request .= "    <Package>
      <PackagingType>
        <Code>02</Code>
        <Description></Description>
      </PackagingType>
      <Description>Rate</Description>
      <PackageWeight>
        <UnitOfMeasurement>
          <Code>LBS</Code>
        </UnitOfMeasurement>
        <Weight>$package[weight]</Weight>
      </PackageWeight>
    </Package>";
		}

		$request .= "    <ShipmentServiceOptions />
  </Shipment>
</RatingServiceSelectionRequest>";

		if (self::$settings->getBoolean("testing", false))
			curl_setopt($ch, CURLOPT_URL, "https://www.ups.com/ups.app/xml/Rate");
		else
			curl_setopt($ch, CURLOPT_URL, "https://wwwcie.ups.com/ups.app/xml/Rate");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$data = curl_exec($ch);
		curl_close($ch);

		$xml = simplexml_load_string($data);

		$statusCode = (integer) $xml->Response->ResponseStatusCode;
		if ($statusCode != 1)
			throw new Exception((string) $xml->Response->Error->ErrorDescription);

		$quotes = Array();
		foreach ($xml->RatedShipment as $quote) {
			$code = (integer) $quote->Service->Code;
			$name = array_key_exists($code, self::$services) ? self::$services[$code] : "Unknown Service $code";
			array_push($quotes, Array(
				"code" => $code,
				"name" => $name,
				"cost" => (float) $quote->TotalCharges->MonetaryValue
			));
		}

		return $quotes;
	}

}
?>
