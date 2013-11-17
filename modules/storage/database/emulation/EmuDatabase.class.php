<?php
abstract class EmuDatabase extends DatabaseInstance {

	public abstract function listColumns();
	protected abstract function nextEntry();
	protected abstract function skipEntry();
	protected abstract function tryCountEntries();

	public function getName() {
		return false;
	}

	public function queryRows($table, $where = Array(), $start = -1, $limit = -1, $orderBy = false) {
		$results = Array();
		$count = $this->tryCountEntries();
		$total = 0;

		foreach ($where as $key => & $value) {
			if (startsWith($key, "LIKE "))
				$value = WildcardMatch::instance($value, "%");
		}

		if (!$orderBy) {
			while ($start > 0 && $this->skipEntry()) {
				$start--;
				$total++;
			}

			while ($limit > 0 && ($entry = $this->nextEntry())) {
				$skip = false;

				foreach ($where as $key => $value) {
					if (startsWith($key, "LIKE ")) {
						$key = substr($key, 5);
						if (!$value->exactMatch($entry[$key])) {
							$skip = true;
							break;
						}
					} else
						if ($entry[$key] != $value) {
							$skip = true;
							break;
						}
				}

				if (!$skip) {
					array_push($results, $entry);
					$limit--;
				}

				$total++;
			}

			if (!$count)
				while ($this->skipEntry()) {
					$total++;
				}
		}

		return Array("total" => $count ? $count : $total, "results" => $results);
	}

}
?>
