<?php
abstract class BasicEmuDatabase extends EmuDatabase {

	private $entries = false;
	private $entryCount = 0;

	private function initEntries() {
		if ($this->entries === false) {
			$this->entries = $this->getEntries();
			$this->entryCount = count($this->entries);
		}
	}

	protected function tryCountEntries() {
		$this->initEntries();

		return $this->entryCount;
	}

	public function listColumns() {
		$this->initEntries();

		return count($this->entries) > 0 ? array_keys($this->entries[0]) : Array();
	}

	protected function nextEntry() {
		if ($this->entries === false)
			$this->entries = $this->getEntries();

		return count($this->entries) > 0 ? array_shift($this->entries) : false;
	}

	protected function skipEntry() {
		if ($this->entries === false)
			$this->entries = $this->getEntries();

		if (count($this->entries)) {
			array_shift($this->entries);
			return true;
		} else
			return false;
	}

	protected abstract function getEntries();

}
?>
