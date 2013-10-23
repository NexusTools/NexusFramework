<?php
abstract class GitHubAPIClassConstructorCachedObject extends CachedObject {
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
		return "GitHub-API-Class-Constructor";
	}

	protected function needsUpdate() {
		return false;
	}

	protected function getLifetime() {
		return 60 * 60 * 60;
	}
}
?>
