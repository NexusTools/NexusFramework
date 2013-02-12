<?php
class GoogleWeather extends CachedObject {

	const IMG_URL_PREFIX = "http://www.google.ca/ig/images/weather/";

	public function getID() {
		return "google-weather";
	}
	
	protected function getLifetime() {
		return 60*60*4; // every 2 hours
	}
	
	protected function needsUpdate() {
		return true;
	}
	
	protected function updateMeta(&$metaObject){
	}
	
	public function isValid() {
		return false;
	}
	
	public static function getIconURL($condition){
		$path = Framework::getReferenceURI("weather_icons/$condition");
		if(!$path)
			return self::IMG_URL_PREFIX . $condition;
		return $path;
	}
	
	protected function isShared(){
		return true;
	}
	
	public static function fTc($Tf){
		return round((5/9)*($Tf-32));
	}

	public function update(){
		$weather_feed = file_get_contents("http://www.google.com/ig/api?weather=Ontario+KW");
		if(!$weather_feed)
			throw new Exception("Unable to fetch `http://www.google.com/ig/api?weather=Ontario+KW`");
		$weather = simplexml_load_string($weather_feed);
		if(!$weather)
			throw new Exception("Data returned not valid xml");
		
		$data = Array();
		$data['current'] = Array(
			'condition' => (string)$weather->weather->current_conditions->condition['data'],
			'temp' => (string)$weather->weather->current_conditions->temp_c['data'],
			'humidity' => (string)$weather->weather->current_conditions->humidity['data'],
			'icon' => (string)$weather->weather->current_conditions->icon['data'],
			'wind_condition' => (string)$weather->weather->current_conditions->wind_condition['data']
								);
								
		$data['current']['icon'] = substr($data['current']['icon'], 19);
		$data['forcast'] = Array();
		foreach($weather->weather->forecast_conditions as $forcast_entry){
			$forcast = Array();
			$forcast['day'] = (string)$forcast_entry->day_of_week['data'];
			$forcast['low'] = (string)$forcast_entry->low['data'];
			$forcast['high'] = (string)$forcast_entry->high['data'];
			$forcast['icon'] = (string)$forcast_entry->icon['data'];
			$forcast['icon'] = substr($forcast['icon'], 19);
			array_push($data['forcast'], $forcast);
		}
		return $data;
	}

	public function getPrefix(){
		return "widgets";
	}

}
?>
