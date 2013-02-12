<?php
abstract class EmuDatabase {

	protected abstract function getEntries();
	
	public function getName(){
	    return false;
	}
	
	public function __pregCallbackLikePattern($matches){
		if($matches[0] == "%")
			return ".*";
		else
			return preg_quote($matches[0]);
	}
	
	public function queryRows($table, $where, $start, $limit, $orderBy=false){
		$realEntries = $this->getEntries();
	
		$results = Array();
		
		if($orderBy){
			$column = $orderBy[0];
		
			$orderedEntries = Array();
			foreach($realEntries as $entry){
				if(!isset($orderedEntries[$entry[$column]]))
					$orderedEntries[$entry[$column]] = Array();
					
				array_push($orderedEntries[$entry[$column]], $entry);
			}
			
			if($orderBy[1] == "ASC")
				ksort($orderedEntries);
			else
				krsort($orderedEntries);
				
			$realEntries = Array();
			foreach($orderedEntries as $entryArray){
				foreach($entryArray as $entry)
					array_push($realEntries, $entry);
			}
		}
		
		if($where) {
			$filteredEntries = Array();
		
			foreach($where as $key => &$value){
				if(startsWith($key, "LIKE "))
					$value = "/^" . preg_replace_callback("/(%|[^%]+)/",
							Array(__CLASS__, "__pregCallbackLikePattern"), $value) . "$/i";
			}
			
			foreach($realEntries as $key => $extension) {
				$skip = false;
				foreach($where as $key => $value){
					if(startsWith($key, "LIKE ")){
						$key = substr($key, 5);
						if(!preg_match($value, $extension[$key])) {
							$skip = true;
							continue;
						}
					} else if($extension[$key] != $value) {
						$skip = true;
						break;
					}
				}
				if($skip)
					continue;
					
				array_push($filteredEntries, $extension);
			}
			
			$realEntries = $filteredEntries;
		}
		
		foreach($realEntries as $key => $extension){
			if($start) {
				$start--;
				continue;
			}
		
			array_push($results, $extension);
		
			if(!--$limit)
				break;
		}
		
		$entries = Array();
		$entries['total'] = count($realEntries);
		$entries['results'] = $results;
		return $entries;
	}	
	
}
?>
