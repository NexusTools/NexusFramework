<?php
class SearchCore {

	private static $handlers = array("General" => array("template" => "{{name}}", "handlers" => array()));

	public static function registerHandler($expr, $callback, $subsection = "Miscellaneous", $section = "General") {
		if (!array_key_exists($section, self::$handlers))
			throw new Exception("Section `$section` not registered...");

		if (!array_key_exists($subsection, self::$handlers[$section]))
			self::$handlers[$section]['handlers'][$subsection] = array();

		self::$handlers[$section]['handlers'][$subsection][$expr] = $callback;
	}

	public static function registerSection($name, $template = "{{name}}") {
		if (array_key_exists($name, self::$handlers))
			throw new Exception("Section `$section` already registered...");
		self::$handlers[$name] = array("template" => $template, "handlers" => array());
	}

	public static function isTyping($query, $check) {
		$firstBreak = true;

		while (($len = strlen($check)) > 0) {
			if (substr_compare($query, $check, -$len, $len, true) === 0) {
				if ($firstBreak) {
					if ($notify)
						$activeNotify = $notify;

					return -1; // Ends with a space, so already entered
					}

				return $len;
			}
			$firstBreak = false;

			$check = substr($check, 0, $len - 1);
		}

		return false;
	}

	public static function checkSuggestions($query, $suggestions, &$activeNotify = NULL) {
		$data = array();
		$activeNotify = false;
		foreach ($suggestions as $suggest => $sQuery) {
			$notify = false;
			if (is_array($sQuery)) {
				$notify = $sQuery[1];
				$sQuery = $sQuery[0];
			}

			if (is_numeric($suggest)) {
				$suggest = "... $sQuery";
				$sQuery = " $sQuery ";
			}

			if (($pos = self::isTyping($query, $sQuery)) !== false) {
				if ($pos === - 1)
					$activeNotify = $notify;
				else
					array_push($data, array("suggest" => $suggest,
						"query" => $query.substr($sQuery, $pos)));
			}
		}

		if (!count($data) && $activeNotify)
			array_push($data, array("notify" => $activeNotify));
		return $data;
	}

	public static function sortAndProcessResults($results) {

		$sortedData = array();
		foreach ($results as $entry) {
			$match = $entry['match'];
			if (is_array($match)) {
				$avgMatch = 0;
				foreach ($match as $m)
					$avgMatch += $m;
				$match = round($avgMatch / count($match));
			} else
				$match = round($match);
			$entry['match'] = $match / 100; // Prefer to use Floats Client Side
			$match = (string) $match;
			if (!array_key_exists($match, $sortedData))
				$sortedData[$match] = array($entry);
			else
				array_push($sortedData[$match], $entry);
		}
		krsort($sortedData);

		$results2 = array();
		foreach ($sortedData as $key => $entryArray)
			$results2 = array_merge($results2, $entryArray);

		if (count($results2) != count($results))
			OutputFilter::startRawOutput();
		return $results2;
	}

	public static function search($query, $start = 0, $limit = 10, $filters = null) {
		$total = 0;
		$errors = array();
		$results = array();
		$templates = array();
		$suggestions = array();
		$notification = false;
		foreach (self::$handlers as $section => $subsections) {
			$sectionData = array();
			foreach ($subsections['handlers'] as $subsection => $handler) {
				foreach ($handler as $expr => $callback) {
					try {
						$ret = preg_match("#^$expr\\s*$#i", $query, $matches);
						if ($ret === false)
							throw new Exception("$section:$subsection provided bad expression format.");

						if (!$ret)
							continue; // No match

						$res = call_user_func($callback, $matches, $filters);
						if (!is_array($res) || !count($res))
							continue; // No results

						foreach ($res as $entry) {
							if (array_key_exists("notify", $entry)) {
								$notification = $entry['notify'];
								continue;
							}
							if (array_key_exists("suggest", $entry)) {
								if (!array_key_exists("query", $entry))
									throw new Exception("Entry has suggest attribute but no query attribute");

								array_push($suggestions, $entry);
								continue;
							}
							if (!array_key_exists("ref", $entry))
								throw new Exception("Entry missing ref attribute");
							if (!array_key_exists("match", $entry))
								throw new Exception("Entry missing match attribute");

							if (array_key_exists($entry['ref'], $sectionData)) {
								if (!is_array($sectionData[$entry['ref']]['match']))
									$sectionData[$entry['ref']]['match'] = array(
										$sectionData[$entry['ref']]['match']);
								array_push($sectionData[$entry['ref']]['match'], $entry['match']);
							} else
								$sectionData[$entry['ref']] = $entry;
						}
					} catch (Exception $e) {
						array_push($errors, $e->getMessage());
					}
				}
			}

			$count = count($sectionData);
			if ($count > 0) {
				$total += $count;
				$templates[$section] = $subsections['template'];
				$results[$section] = array_slice(self::sortAndProcessResults($sectionData), $start, $limit);
			}
		}

		$data = array();
		if (count($suggestions) > 0)
			$data["suggestions"] = $suggestions;
		else
			if ($notification)
				$data['notification'] = $notification;

		if (count($results) > 0) {
			$data["results"] = $results;
			$data["templates"] = $templates;
		}

		if (count($errors) > 0)
			$data["errors"] = $errors;

		return $data;
	}

}
?>
