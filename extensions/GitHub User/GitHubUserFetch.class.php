<?php
class GitHubUserFetch extends GitHubAPICachedObject {
	private $lookupParams;
	private $isOrg;
	public function __construct($lookupParams = null, $isOrg = false) {
		$this->lookupParams = $lookupParams;
		$this->isOrg = $isOrg;
	}

	protected function update() {
		$url = '/users';
		if ($this->lookupParams !== "__all__") {
			if ($isOrg) {
				if ($this->lookupParams != null) {
					$url = '/orgs/'.$this->lookupParams;
				}
			} else {
				if ($this->lookupParams != null) {
					$url = '/users/'.$this->lookupParams;
				} else
					if (GitHubAPI::getInstance()->isAuthenticated()) {
						$url = '/user';
					}
			}
		}
		return GitHubAPI::getInstance()->request($url);
	}

	public function getID() {
		return Framework::uniqueHash('userentry'.GitHubAPI::getInstance()->isAuthenticated().$this->lookupParams.$this->isOrg);
	}
}
?>
