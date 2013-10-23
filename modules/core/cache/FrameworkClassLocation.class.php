<?php
class FrameworkClassLocation extends CachedFileBase {

	private $name;

	public function __construct($nam) {
		CachedFileBase::__construct(FRAMEWORK_MODULE_PATH);
		$this->name = $nam.".class.php";
	}

	protected function getAdvancedID() {
		return $this->name;
	}

	protected function updateAdvancedMeta(&$meta) {
	}

	public function getMimeType() {
		return "application/octet-stream";
	}

	public function getPrefix() {
		return "framework-class-locator";
	}

	protected function searchPath($path) {
		if ($handle = opendir($path)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry == "." || $entry == "..")
					continue;

				$file = $path.$entry;
				if (is_dir($file) && is_string($ret = $this->searchPath($file.DIRSEP)))
					return $ret;

				if ($entry == $this->name)
					return $file;
			}

			closedir($handle);
		}
		return false;
	}

	public function update() {
		return $this->searchPath(FRAMEWORK_MODULE_PATH);
	}

	protected function isShared() {
		return true;
	}

}
?>
