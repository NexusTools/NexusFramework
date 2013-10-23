<?php
class CacheDatabase extends BasicEmuDatabase {

	private static $instance;

	public static function getInstance() {
		return self::$instance ? self::$instance : (self::$instance = new CacheDatabase());
	}

	private function __construct() {
	}

	private function processSection(&$array, $path, $section) {
		$dir = opendir("$path$section");
		while (($entry = readdir($dir)) !== false) {
			if (!endsWith($entry, ".meta.dat"))
				continue;

			$entryData = Array();
			$entryData['name'] = substr($entry, 0, strlen($entry) - 9);
			$entryData['section'] = $section;

			$metaData = unserialize(file_get_contents("$path$section".DIRSEP.$entry));
			if (isset($metaData['p']))
				$entryData['path'] = shortpath($metaData['p']);
			else
				$entryData['path'] = "";
			$entryData['provider'] = $metaData['pv'];
			$entryData['expires'] = $metaData['n'];
			$entryData['rowid'] = crc32($section.$entryData['name']);

			array_push($array, $entryData);
		}
		closedir($dir);
	}

	public function getEntries() {
		return Array();

		$entries = Array();
		$dir = opendir($path = TMP_PATH."cache".DIRSEP);
		while (($section = readdir($dir)) !== false) {
			if ($section == "." || $section == "..")
				continue;

			self::processSection($entries, $path, $section);
		}
		closedir($dir);
		$dir = opendir($path = SHARED_TMP_PATH."cache".DIRSEP);
		while (($section = readdir($dir)) !== false) {
			if ($section == "." || $section == "..")
				continue;

			self::processSection($entries, $path, $section);
		}
		closedir($dir);
		return $entries;
	}

}
?>
