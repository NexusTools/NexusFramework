<?php // Relies on GeoIP Extension
class RegionCore {

	public static function getRecord($addr=false) {
		try {
			if(!$addr)
				$addr = ClientInfo::getRemoteAddress();
			$record = geoip_record_by_name($addr);
			$record['timezone'] = geoip_time_zone_by_country_and_region($record['country'], $record['region']);
			$record['postal-code'] = $record['postal_code'];
			
			return $record;
		} catch(Exception $e) {
			return false;
		}
	}

}
?>
