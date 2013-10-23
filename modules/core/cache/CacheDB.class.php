<?php
abstract class CacheDB {

	private static $instances = new ReferenceMap();
	private $entryCache = new ReferenceMap();

	public static function getInstance($prefix = "cache") {
		$instance = self::$instances->getValue($prefix);
		if ($instance === null) {
			try {
				$instance = MemcachedDB::instance($prefix);
			} catch (Exception $e) {
			}

			if ($instance === null)
				$instance = FilesystemCacheDB::instance($prefix);

			self::$instances->setValue($prefix, $instance);
		}
		return $instance;
	}

	public abstract function clean();
	public abstract function clear();
	public abstract function lookupEntry($key, $create = true);

	public function getLocalStorage($key) {
		$entry = $entryCache->getEntry($key);
		$entry = $this->getLocalStorage();
	}

	public function getEntry($key, $create = true) {
		$entry = $entryCache->getValue($key);
		if ($entry === null) {
			$entry = $this->lookupEntry($key, $create);
			if ($create)
				$this->entryCache->setValue($key, $entry);
		}

		return $entry;
	}

	public function setValue($key, $val = true) {
		$entry = $this->getEntry($key);
		return $entry->setContent($val);
	}

	public function getValue($key, $def = false) {
		$entry = $this->getEntry($key, false);
		if ($entry)
			return $entry->setContent($val);
		return $def;
	}

	public function touch($key) {
		$entry = $this->getEntry($key);
		return $entry->touch($val);
	}

	public function delete($key) {
		$entry = $this->getEntry($key, false);
		if ($entry)
			return $entry->delete($val);
		return true;
	}

}
?>
