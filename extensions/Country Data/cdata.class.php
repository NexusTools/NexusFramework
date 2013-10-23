<?php
class CountryData {

	const ONLY_NAME = 0x0;
	const COUNTRY_CODE = 0x1;
	const LONG_COUNTRY_CODE = 0x2;
	const LONG_NAME = 0x10;

	public static function getCodeForCountry($country, $longName = false, $longCode = false) {
		$lookup = new CountryDataLookup($longName ? 'official_name' : 'name', $country, $longCode ? "alpha_3_code" : "alpha_2_code");
		return $lookup->getData() ? $lookup->getData() : $country;
	}

	public static function getCountryForCode($country, $longName = false, $longCode = false) {
		$lookup = new CountryDataLookup($longCode ? "alpha_3_code" : "alpha_2_code", $country, $longName ? 'official_name' : 'name');
		return $lookup->getData() ? $lookup->getData() : $country;
	}

	public static function listCountries($mode = self::COUNTRY_CODE) {
		if ($mode >= 0x10) {
			$mode -= 0x10;
			$key = 'official_name';
		} else
			$key = 'name';
		switch ($mode) {
		case 0x0:
			$value = null;
			break;

		case 0x1:
			$value = 'alpha_2_code';
			break;

		case 0x2:
			$value = 'alpha_3_code';
			break;

		default:
			throw new Exception("Invalid Mode");
		}
		$query = new CountryDataParser($key, $value);
		return $query->getData();
	}

	public static function listStatesForCountry($country, $mode = self::COUNTRY_CODE) {
		if ($mode >= 0x10) {
			$mode -= 0x10;
			$key = 'official_name';
		} else
			$key = 'name';
		switch ($mode) {
		case 0x0:
			$value = null;
			break;

		case 0x1:
			$value = 'alpha_2_code';
			break;

		case 0x2:
			$value = 'alpha_3_code';
			break;

		default:
			throw new Exception("Invalid Mode");
		}
		$query = new StateDataParser(self::getCodeForCountry($country), $key, $value);
		return $query->getData();
	}

	//public static function getStates($country, $mode = self::ONLY_NAME){
	//	return self::$data[$country];
	//}

}
?>
