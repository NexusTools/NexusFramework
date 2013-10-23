<?php // Relies on GeoIP Extension
class RegionCore {

	public static function getRecord($addr = false) {
		try {
			if (!$addr)
				$addr = ClientInfo::getRemoteAddress();
			$record = geoip_record_by_name($addr);
			if (!$record)
				throw new Exception("No Record Returned");

			$data = Array();
			foreach ($record as $key => $val) {
				if (strlen($val) < 1)
					continue;
				$key = str_replace("_", "-", $key);
				$data[$key] = $val;
			}
			unset($record);

			if (array_key_exists("country-code", $data) && array_key_exists("region", $data))
				$data['timezone'] = geoip_time_zone_by_country_and_region($data['country-code'], $data['region']);

			return $data;
		} catch (Exception $e) {
			return Array("error" => $e->getMessage());
		}
	}

}
?>
