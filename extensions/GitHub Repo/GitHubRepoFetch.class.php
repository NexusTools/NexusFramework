<?php
class GitHubRepoFetch extends GitHubAPICachedObject {
	private $lookupParams;
	private $isOrg;
	private $getBranches;
	private $repoName;
	private $branchName;

	public function __construct($lookupParams = null, $isOrg = false, $repoName = null, $branchName = null) {
		$this->lookupParams = $lookupParams;
		$this->isOrg = $isOrg;
		$this->getBranches = $getBranches;
		$this->repoName = $repoName;
		$this->branchName = $branchName;
	}

	protected function update() {
		$url = '/repositories';
		if ($this->lookupParams !== "__all__") {
			if ($this->repoName || ($this->repoName && $this->branchName)) {
				$url = '/repos/'.$this->lookupParams.'/'.$this->repoName.'/branches'.($this->branchName ? '/'.$this->branchName : '');
			} else {
				if ($this->isOrg) {
					if ($this->lookupParams != null) {
						$url = '/orgs/'.$this->lookupParams.'/repos';
					}
				} else {
					if ($this->lookupParams != null) {
						$url = '/users/'.$this->lookupParams.'/repos';
					} else
						if (GitHubAPI::getInstance()->isAuthenticated()) {
							$url = '/user/repos';
						}
				}
			}
		}
		return GitHubAPI::getInstance()->request($url);
	}

	public function getID() {
		return Framework::uniqueHash('repoentry'.GitHubAPI::getInstance()->isAuthenticated().$this->lookupParams.$this->isOrg.$this->repoName.$this->branchName);
	}
}
?>
