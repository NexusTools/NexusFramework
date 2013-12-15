<?php
class Theme extends CachedFile {

	private static $activePath;
	private $parent = false;

	protected static function findThemePath($name) {
		if (file_exists($path = INDEX_PATH."themes".DIRSEP.$name))
			return $path;

		if (file_exists($path = FRAMEWORK_RES_PATH."themes".DIRSEP.$name))
			return $path;

		return false;
	}

	public static function initActivePath() {
		if (!defined("THEME"))
			define("THEME", "Basic");
		self::$activePath = self::findThemePath(THEME);
	}

	public static function getPath() {
		return self::$activePath;
	}

	public function __construct($path) {
		parent::__construct($path);
	}

	public function getMimeType() {
		return "inode/directory";
	}

	public function getPrefix() {
		return "theme";
	}

	public function findFile() {
	}

	protected function update() {
		$data = Array();

		if (file_exists($this->getFilepath()."info.json")) {
			$data['metadata'] = LocalFile::getContentFor($this->getFilepath()."info.json");
			if (!$data['metadata'])
				throw new Exception("Theme info.json file corrupt");
		}

		if (file_exists($this->getFilepath()."head.inc.php"))
			$data['hs'] = "head.inc.php";

		if (file_exists($this->getFilepath()."tmpl.head.inc.php"))
			$data['ths'] = "tmpl.head.inc.php";
		if (file_exists($this->getFilepath()."body.head.inc.php"))
			$data['bhs'] = "body.head.inc.php";

		if (file_exists($this->getFilepath()."tmpl.foot.inc.php"))
			$data['tfs'] = "tmpl.foot.inc.php";
		if (file_exists($this->getFilepath()."body.foot.inc.php"))
			$data['bfs'] = "body.foot.inc.php";

		$data['classes'] = Array();
		if (file_exists($this->getFilepath()."classes")) {

			foreach (glob($this->getFilepath()."classes/*.class.php", GLOB_NOSORT) as $classfile) {
				$handle = fopen($classfile, "r");

				if (is_resource($handle)) {
					while (($buffer = fgets($handle, 4096)) !== false) {
						if (startsWith($buffer, "class ")) {
							$parts = explode(" ", $buffer);
							$data['cl'][$parts[1]] = substr($classfile, strlen($this->getFilepath()));
							break;
						} else
							if (startsWith($buffer, "abstract class ")) {
								$parts = explode(" ", $buffer);
								$data['cl'][$parts[2]] = substr($classfile, strlen($this->getFilepath()));
								break;
							}
					}
					fclose($handle);
				} else
					throw new IOException($classfile, IOException::ReadAccess);
			}
		}

		return $data;
	}

	protected function isShared() {
		return true;
	}

	public function initialize() {
		if ($this->hasKey("error"))
			throw new Exception($this->getValue('error'));

		if ($this->hasKey("metadata")) {
			$metadata = $this->getValue("metadata");
			if (array_key_exists("parent", $metadata)) {
				$this->parent = new Theme(self::findThemePath($metadata["parent"]));
				$this->parent->initialize();
			}
		}

		if ($this->hasKey('hs'))
			require_chdir($this->getValue('hs'), $this->getFilepath());

		if ($this->hasKey("cl"))
			ClassLoader::registerClasses($this->getValue("cl"));
		self::$activePath = $this->getFilepath();
	}

	public function runHeader() {
		if ($this->hasKey("ths"))
			require_chdir($this->getValue('ths'), $this->getFilepath());

		if ($this->hasKey("bhs")) {
			echo "<header>";
			require_chdir($this->getValue('bhs'), $this->getFilepath());
			echo "</header>";
		} else
			if ($this->parent)
				$this->parent->runHeader();
	}

	public function runFooter() {
		if ($this->hasKey("bfs")) {
			echo "<footer>";
			require_chdir($this->getValue('bfs'), $this->getFilepath());
			echo "</footer>";
		} else
			if ($this->parent)
				$this->parent->runFooter();

		if ($this->hasKey("tfs"))
			require_chdir($this->getValue('tfs'), $this->getFilepath());
	}

}
Theme::initActivePath();
?>
