<?php
class Triggers {

	private static $watchers = Array();
	private static $moduleWatchers = Array();

	public static function watchModule($module, $callback) {
		if (!isset(self::$moduleWatchers[$module]))
			self::$moduleWatchers[$module] = Array();

		self::$moduleWatchers[$module][] = $callback;
	}

	public static function watchAll($callback) {
		self::$watchers[] = $callback;
	}

	public static function broadcast($module, $event, $arguments = false) {
		$data = Array();
		foreach (self::$watchers as $watcher) {
			$moreData = call_user_func($watcher, $module, $event, $arguments);
			if ($moreData) {
				if (is_array($moreData))
					$data = array_merge($data, $moreData);
				else
					array_push($data, $moreData);
			}
		}

		if (isset(self::$moduleWatchers[$module]))
			foreach (self::$moduleWatchers[$module] as $watcher) {
				$moreData = call_user_func($watcher, $module, $event, $arguments);
				if ($moreData) {
					if (is_array($moreData))
						$data = array_merge($data, $moreData);
					else
						array_push($data, $moreData);
				}
			}

		return $data;
	}

}
?>
