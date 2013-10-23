<widget class="sitemap"><h1>Sitemap</h1><pre><?php

function __siteMap_injectPath(&$siteMap, $title, $url) {
}

function __siteMap_dumpPageFolder(&$siteMap, $root, $folder) {
	global $__siteMap_array;

	if ($handle = opendir($folder)) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry == "." || $entry == ".." || preg_match("/[A-Z]/", $entry)) // Ignore uppercase files
				continue;

			$file = "$folder$entry";
			if (is_dir("$file/")) {
				try {
					if (file_exists("$file/root.exists.inc.php") && !include("$file/root.exists.inc.php"))
						continue;

					if (file_exists("$file/root.cond.inc.php") && !include("$file/root.cond.inc.php"))
						continue;
				} catch (Exception $e) {
					continue;
				}

				__siteMap_dumpPageFolder($siteMap,
					"$root$entry/", "$file/");
			} else {
				if (!preg_match("/(.+)\\.title/i", $entry, $pageFile))
					continue;
				$pageFile = $pageFile[1];

				try {
					if (file_exists("$folder$pageFile.exists.inc.php") && !include("$folder$pageFile.exists.inc.php"))
						continue;

					if (file_exists("$folder$pageFile.cond.inc.php") && !include("$folder$pageFile.cond.inc.php"))
						continue;
				} catch (Exception $e) {
					continue;
				}

				$title = trim(file_get_contents($file));
				if ($pageFile == "root") {
					$url = substr($root, 0, max(1, strlen($root) - 1));
					$pageFile = "";
				} else
					$url = "$root$pageFile";

				$cSiteMap =& $siteMap;
				foreach (Framework::splitPath($url) as $part) {
					var_dump($part);

					if (!array_key_exists($part, $cSiteMap)) {
						$subPath = array();
						$cSiteMap[$part] = array("sub" => $subPath);
						$cSiteMap =& $subPath;
					} else
						$cSiteMap =& $cSiteMap[$part];
				}

				var_dump($url);
				var_dump($cSiteMap);
				$cSiteMap['page'] = array("title" => $title,
					"url" => $url);
			}
		}

		closedir($handle);
	}
}

function __siteMap_dumpVirtualPaths(&$siteMap, $virtualPaths, $root = "/") {
	if (isset($virtualPaths['p']))
		foreach ($virtualPaths['p'] as $path)
			__siteMap_dumpPageFolder($siteMap, $root, $path);

	if (isset($virtualPaths['v']))
		foreach ($virtualPaths['v'] as $path => $vPaths)
			__siteMap_dumpVirtualPaths($siteMap,
				$vPaths, "$root$path/");
}
echo "<pre style=\"text-align: left\">";

$siteMap = array();
Framework::suppressRedirects(true);
__siteMap_dumpPageFolder($siteMap, "/", INDEX_PATH."pages/");
__siteMap_dumpVirtualPaths($siteMap, ExtensionLoader::getVirtualPaths(), '/');
Framework::suppressRedirects(false);

print_r($siteMap);
?></pre></widget>
