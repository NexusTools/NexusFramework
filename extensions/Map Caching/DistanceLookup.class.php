<?php
class MapsDistance extends MapCachedObject {

	private $address1;
	private $address2;

	public function __construct($address1, $address2) {
		$this->address1 = implode(',', MapsLatLng::lookup($address1));
		$this->address2 = implode(',', MapsLatLng::lookup($address2));
		MapCachedObject::__construct($this->address1.'-'.$this->address2);
	}

	public static function lookup($address1, $address2) {
		$mapData = new MapsDistance($address1, $address2);
		return $mapData->getData();
	}

	public function update() {
		return MapsAPI::calculateDistance($this->address1, $this->address2);
	}

}
?>
