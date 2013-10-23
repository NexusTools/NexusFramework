<?php
class AnalyticsScript extends CachedFile {

	private $settings;

	public function __construct() {
		$this->settings = new Settings("Google Analytics");
		CachedFile::__construct($this->settings->getStoragePath());
	}

	public function getPrefix() {
		return "analytics";
	}

	public function isShared() {
		return false;
	}

	public function getMimeType() {
		return "text/javascript";
	}

	public function update() {
		if (strlen($code = $this->settings->getValue("code"))) {
			$script = "var _gaq = _gaq || [];";
			$script .= "_gaq.push(['_setAccount', '$code']);";
			if (strlen($domain = $this->settings->getValue("domain"))) {
				$script .= "_gaq.push(['_setDomainName', '$domain']);";
				$script .= "_gaq.push(['_setAllowLinker', true]);";
			}
			$script .= "_gaq.push(['_trackPageview']);";
			$script .= "(function() {";
			$script .= "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;";
			$script .= "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + ";
			$script .= "'.google-analytics.com/ga.js';";
			$script .= "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);";
			$script .= "})();";
			return $script;
		}
		return null;
	}

}
?>
