<?php
abstract class GitHubAPICachedObject extends CachedObject {
	private $id;

	protected function __construct($id) {
		$this->id = Framework::uniqueHash($id);
	}

	protected function updateMeta(&$meta) {
	}

	public function isValid() {
		return true;
	}

	public function isShared() {
		return true;
	}

	public function getID() {
		return $this->id;
	}

	public function getPrefix() {
		return "GitHub-API";
	}

	protected function needsUpdate() {
		return false;
	}

	protected function getLifetime() {
		return rand(60 * 30 /*30 minutes*/, 60 * 60 * 2 /*2 hours*/);
	}

	public function getExtensionType() {
		return CachedObjectExtensionType::SERIALIZED;
	}

	public function getData() {
		GitHubAPIClassConstructor::getInstance()->loadClasses();
		return parent::getData();
	}
}
?>
