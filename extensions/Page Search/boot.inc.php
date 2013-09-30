<?php

function __pageSearch_explorePageFolder(&$results, $root, $folder, $query) {
	if ($handle = opendir($folder)) {
		while (false !== ($entry = readdir($handle))) {
			if($entry == "." || $entry == ".." ||
					preg_match("/[A-Z]/", $entry)) // Ignore uppercase files
				continue;
				
			$file = "$folder$entry";
			if(is_dir("$file/")) {
				if(file_exists("$file/root.exists.inc.php") ||
						file_exists("$file/root.cond.inc.php"))
					continue;
				
				__pageSearch_explorePageFolder($results,
						"$root$entry/", "$file/", $query);
			} else {
				if(!preg_match("/(.+)\\.title/i", $entry, $pageFile))
					continue;
				$pageFile = $pageFile[0];
				
				if(file_exists("$folder$pageFile.exists.inc.php") ||
						file_exists("$folder$pageFile.cond.inc.php"))
					continue;
				
				$title = trim(file_get_contents($file));
				if($pageFile == "root")
					$pageFile = "";
					
				if(preg_match("/" . preg_quote($query) . "/i", $title, $matches)) {
					similar_text($query, $title, $match);
					array_push($results, array("ref" =>"page:" .
							Framework::uniqueHash("$root$entry"),
							"url" => "$root$entry", "title" => $title,
							"match" => $match));
				}
		    }
		}

		closedir($handle);
	}
}
	
function __pageSearch_exploreVirtualPaths(&$results, $virtualPaths, $root="/", $query){
	if(isset($virtualPaths['p']))
		foreach($virtualPaths['p'] as $path)
			__pageSearch_explorePageFolder($results, $root, $path, $query);
	
	if(isset($virtualPaths['v']))
		foreach($virtualPaths['v'] as $path => $vPaths)
			__pageSearch_exploreVirtualPaths($results,
						$vPaths, "$root$path/", $query);
}

function __pageSearch_Callback($matches, $filters) {
	$results = array();
	__pageSearch_explorePageFolder($results, "/", INDEX_PATH . "pages/", $matches[0]);
	__pageSearch_exploreVirtualPaths($results, ExtensionLoader::getVirtualPaths(),
																'/', $matches[0]);
	return $results;
}

SearchCore::registerSection("Pages", "{{title}}
{{small}}{{url}}{{endsmall}}");
SearchCore::registerHandler(".+", "__pageSearch_Callback", "PageTitle", "Pages");

?>
