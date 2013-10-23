<?php
class MapsAPI {

	private static $cachePath = false;

	public static function getData($url) {
		if (!self::$cachePath) {
			self::$cachePath = SHARED_TMP_PATH."maps".DIRSEP;
			if (!is_dir(self::$cachePath) && !mkdir(self::$cachePath))
				throw new Exception("Failed to create cache directory");
		}

		$tempFile = self::$cachePath.Framework::uniqueHash($url);
		$data = is_file($tempFile) ? json_decode(file_get_contents($tempFile), true) : false;

		if (!is_array($data)) {
			$ch = curl_init();
			$timeout = 15;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			if ($data === false)
				throw new Exception("Query Failed: ".curl_error($ch));
			$data = json_decode(trim($data), true);
			if ($data['status'] != "OK")
				throw new Exception("Query Failed: ".$data['status']);
			file_put_contents($tempFile, json_encode($data));
		}

		return $data;
	}

	// meters
	public static function calculateDistance($loc1, $loc2, $mode = "driving") {
		$data = self::getData("http://maps.googleapis.com/maps/api/distancematrix/json?origins=".urlencode($loc1)."&destinations=".urlencode($loc2)."&mode=$mode&sensor=false");
		return $data['rows'][0]['elements'][0]['distance']['value'] / 1000;
	}

	public static function geoencodeLatLng($address) {
		$data = self::getData("http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($address)."&sensor=false");
		return $data['results'][0]['geometry']['location'];
	}

}
?>
