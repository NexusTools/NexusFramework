<?php

function __pageSearch_explorePageFolder(&$results, $root, $folder, $query) {
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

				__pageSearch_explorePageFolder($results,
					"$root$entry/", "$file/", $query);
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

				$reg = "/".preg_quote($query, '/')."/i";
				if (preg_match($reg, $title, $matches)) {
					similar_text($query, $title, $match);
					array_push($results, array("ref" => "page:".
						Framework::uniqueHash("$root$entry"),
						"url" => $url, "title" => $title,
						"match" => $match));
				}
				if (preg_match($reg, $url, $matches)) {
					similar_text($query, $url, $match);
					array_push($results, array("ref" => "page:".
						Framework::uniqueHash("$root$entry"),
						"url" => $url, "title" => $title,
						"match" => $match));
				}
			}
		}

		closedir($handle);
	}
}

function __pageSearch_exploreVirtualPaths(&$results, $virtualPaths, $root = "/", $query) {
	if (isset($virtualPaths['p']))
		foreach ($virtualPaths['p'] as $path)
			__pageSearch_explorePageFolder($results, $root, $path, $query);

	if (isset($virtualPaths['v']))
		foreach ($virtualPaths['v'] as $path => $vPaths)
			__pageSearch_exploreVirtualPaths($results,
				$vPaths, "$root$path/", $query);
}

function __pageSearch_Callback($matches, $filters) {
	$results = array();
	Framework::suppressRedirects(true);
	__pageSearch_explorePageFolder($results, "/", INDEX_PATH."pages/", $matches[0]);
	__pageSearch_exploreVirtualPaths($results, ExtensionLoader::getVirtualPaths(),
		'/', $matches[0]);
	Framework::suppressRedirects(false);
	return $results;
}

SearchCore::registerSection("Pages", "{{title}}
{{small}}{{url}}{{endsmall}}");
SearchCore::registerHandler(".+", "__pageSearch_Callback", "PageTitle", "Pages");
?>
