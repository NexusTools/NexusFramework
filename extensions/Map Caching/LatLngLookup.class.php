<?php
class MapsLatLng extends MapCachedObject {

	private $address;

	public function __construct($address) {
		$this->address = $address;
		MapCachedObject::__construct($address);
	}

	public static function lookup($address) {
		$mapData = new MapsLatLng($address);
		return $mapData->getData();
	}

	protected function update() {
		return MapsAPI::geoencodeLatLng($this->address);
	}

}
?>
