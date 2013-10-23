<?php
class UserRegion {

	private $database;
	private $city;
	private $region; // Province or State
	private $country;
	private $radius; // In Kilometers
	private $latitude;
	private $longitude;
	private $postal;

	public function __construct(&$user) {
		$this->database = Database::getInstance();
		$data = $this->database->selectRow("account", Array("id" => $user->getID()));
		if ($data) {
			$this->city = $data['city'];
			$this->country = $data['country'];
			$this->region = $data['region'];
			$this->postal = $data['postal'];
			$this->latitude = $data['latitude'];
			$this->longitude = $data['longitude'];
			$this->radius = $data['radius'];
		} else {
			try {
				$record = geoip_record_by_name(ClientInfo::getRemoteAddress());
				$this->city = $record['city'];
				$this->country = $record['country-code'];
				$this->region = $record['region'];
				$this->postal = $record['postal-code'];
				//$user->setTimeZone(geoip_time_zone_by_country_and_region($this->country, $this->region));
				$this->latitude = $record['latitude'];
				$this->longitude = $record['longitude'];
			} catch (Error $e) {
				$this->city = "Kitchener";
				$this->country = "CA";
				$this->region = "ON";
				//$user->setTimeZone("America/Rainy_River");
				$this->latitude = 43.45;
				$this->longitude = -80.5;
			}

			$this->radius = 20;
			$this->database->insert("account", Array(
				"id" => $user->getID(),
				"radius" => $this->radius,
				"country" => $this->country,
				"region" => $this->region,
				"city" => $this->city,
				"latitude" => $this->latitude,
				"longitude" => $this->longitude,
				"postal" => $this->postal
			));
		}
	}

	public function getState() {

	}

	public function getProvince() {
		return $this->province;
	}

	public function getTimeOffset() {
		return $this->timezone;
	}

}
?>
