<?php
class SearchCore {

	private static $handlers = array("General" => array("template" => "{{name}}", "handlers" => array()));

	public static function registerHandler($expr, $callback, $subsection="Miscellaneous", $section="General") {
		if(!array_key_exists($section, self::$handlers))
			throw new Exception("Section `$section` not registered...");
		
		if(!array_key_exists($subsection, self::$handlers[$section]))
			self::$handlers[$section]['handlers'][$subsection] = array();
		
		self::$handlers[$section]['handlers'][$subsection][$expr] = $callback;
	}
	
	public static function registerSection($name, $template="{{name}}") {
		if(array_key_exists($name, self::$handlers))
			throw new Exception("Section `$section` already registered...");
		self::$handlers[$name] = array("template" => $template, "handlers" => array());
	}
	
	public static function search($query, $start=0, $limit=10, $filters=null) {
		$total = 0;
		$errors = array();
		$results = array();
		foreach(self::$handlers as $section => $subsections) {
			$sectionData = array();
			foreach($subsections['handlers'] as $subsection => $handler) {
				foreach($handler as $expr => $callback) {
					try {
						$ret = preg_match("#^$expr$#i", $query, $matches);
						if($ret === false)
							throw new Exception("$section:$subsection provided bad expression format.");
							
						if(!$ret)
							continue; // No match
						
						$res = call_user_func($callback, $matches, $filters);
						if(!count($res))
							continue; // No results
						
						foreach($res as $entry) {
							if(!array_key_exists("ref", $entry))
								throw new Exception("Entry missing ref attribute");
							if(!array_key_exists("match", $entry))
								throw new Exception("Entry missing match attribute");
								
							if(array_key_exists($entry['ref'], $sectionData)) {
								if(!is_array($sectionData[$entry['ref']]['match']))
									$sectionData[$entry['ref']]['match'] = array(
												$sectionData[$entry['ref']]['match']);
								array_push($sectionData[$entry['ref']]['match'], $entry['match']);
							} else
								$sectionData[$entry['ref']] = $entry;
						}
					} catch(Exception $e) {
						array_push($errors, "$section:$subsection:$e");
					}
				}
			}
			
			$count = count($sectionData);
			if($count > 0) {
				$total += $count;
				
				$sortedData = array();
				foreach($sectionData as $entry) {
					$match = $entry['match'];
					if(is_array($match)) {
						$avgMatch = 0;
						foreach($match as $m)
							$avgMatch += $m;
						$match = round($avgMatch / count($match));
					} else
						$match = round($match);
					$entry['match'] = $match / 100; // Prefer to use Floats Client Side
					
					if(!array_key_exists($match, $sortedData))
						$sortedData[$match] = array($entry);
					else
						array_push($sortedData[$match], $entry);
				}
				krsort($sortedData);
				
				$sectionData = array();
				foreach($sortedData as $key => $entryArray)
					$sectionData = array_merge($sectionData, $entryArray);
				
				$results[$section] = array_slice($sectionData, $start, $limit);
			}
		}
		
		return array("results" => $results, "errors" => $errors);
	}

}
?>
