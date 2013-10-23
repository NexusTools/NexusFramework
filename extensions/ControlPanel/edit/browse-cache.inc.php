<?php
function unlinkRecursive($file) {
	$count = 0;
	if (is_dir($file)) {
		$path = $file.DIRSEP;
		$dir = opendir($path);
		while (($file = readdir($dir)) !== false) {
			if ($file == ".." || $file == ".")
				continue;

			$count += unlinkRecursive($path.$file);
		}
		closedir($dir);
	}
	try {
		if (endsWith($file, ".meta.dat") && unlink($file))
			$count++;
	} catch (Exception $e) {
	}
	return $count;
}

if (isset($_GET['clear'])) {
	$count = unlinkRecursive(BASE_TMP_PATH);
	echo "<banner class=\"success\">$count Cached Files Removed. Cache Cleared!</banner>";
}

ControlPanel::renderManagePage(CacheDatabase::getInstance(), "cache", Array("path" => Array(
	"display" => "Name",
	"value" => "{{name}}\n{{small}}{{path}}{{endsmall}}"
), "section", "provider", "expires" => Array(
	"display" => "Expires",
	"render" => "StringFormat::formatTimeUntil"
)), Array(
), false, Array(
	"delete" => Array(
		"text" => "Clear Entirely",
		"page" => "Clear Cache",
		"popup" => true
	)
), "path");
?>
