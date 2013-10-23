<?php
class ControlPanelPageDefinition extends CachedFile {

	public function __construct($path) {
		parent::__construct($path);
	}

	public function update() {
		$data = json_decode(file_get_contents($this->getFilepath()), true);
		if (!$data)
			return "<banner class=\"error\">Page Definition Corrupt.</banner>";
		return ControlPanel::generatePageScript($data['type'], $data);
	}

	public function isShared() {
		return false;
	}

	public function getPrefix() {
		return "controlpanel";
	}

	public function getMimeType() {
		return "text/html";
	}

}
?>
