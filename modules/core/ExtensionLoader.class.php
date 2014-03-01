<?php
class ExtensionLoader extends CachedFile {

	private static $loadedFeatures = Array();
	private static $loadedExtensions = Array();
	private static $extensionInfoFiles = Array();
	private static $instance = false;
	private static $apis = Array();
	private static $vpaths = Array("p" => Array(), "v" => Array());
	private static $enabled = Array();
	private static $enabledFeatures = Array();

	public function __construct() {
		if (!is_file(EXT_PATH."enabled.json"))
			return;
		parent::__construct(EXT_PATH."enabled.json");
	}

	public function getMimeType() {
		return "text/json";
	}

	public static function loadEnabledExtensions() {
		if (!self::$instance) {
			self::$instance = new ExtensionLoader();
			self::$instance->_loadAll();
		}
	}

	public static function getVirtualPaths() {
		return self::$vpaths;
	}

	public static function isFeatureProvided($feature) {
		return isset(self::$loadedFeatures[$feature]);
	}

	public static function isExtensionLoaded($name) {
		return isset(self::$loadedExtensions[$name]);
	}

	public static function getExtensionInfoFile($name) {
		if (!isset(self::$extensionInfoFiles[$name]))
			return;
		return self::$extensionInfoFiles[$name];
	}

	public static function registerAPICalls() {
		API::registerCallbacks(self::$apis);
	}

	public function getPrefix() {
		return "extension";
	}

	protected function isShared() {
		return true;
	}

	private function readExtension($provides, &$extassoc) {
		if (isset($extassoc[$provides]))
			return;

		if (!self::$enabledFeatures[$provides])
			throw new Exception("No Enabled Extension Provides `$provides`");
		echo "Reading Extension `$provides`\n";

		$path = self::$enabledFeatures[$provides]['path'];
		$infoData = self::$enabledFeatures[$provides]['info'];

		$extdata = Array();
		if (isset($infoData['dependancies'])) {
			$extdata['deps'] = $infoData['dependancies'];
			foreach ($infoData['dependancies'] as $dep) {
				try {
					$this->readExtension($dep, $extassoc);
				} catch (Exception $e) {
					throw new Exception("Failed to Load Dependancy `$dep` for `$provides`", 0, $e);
				}
			}
		}

		chdir($path);
		$extdata['path'] = $path;
		$extdata['name'] = $infoData['name'];
		$extdata['includes'] = glob("includes/*.inc.php");
		if (file_exists("boot.inc.php"))
			array_push($extdata['includes'], "boot.inc.php");

		if (file_exists("api.inc.php"))
			$extdata['api'] = Array($provides => fullpath("api.inc.php"));
		else
			if (is_dir("apis")) {
				$extdata['api'] = Array();
				foreach (glob("apis/*.inc.php", GLOB_NOSORT) as $apiFile) {
					if (preg_match("/^apis\/(.+)\.inc\.php$/", $apiFile, $matches))
						$extdata['api'][$matches[1]] = fullpath($apiFile);
				}
			}

		$extdata['classes'] = Array();
		foreach (glob("*.class.php", GLOB_NOSORT) as $classfile) {
			$handle = @fopen($classfile, "r");
			if ($handle) {
				while (($buffer = fgets($handle, 4096)) !== false) {
					if (startsWith($buffer, "class ")) {
						$parts = explode(" ", $buffer);
						$extdata['classes'][$parts[1]] = $path.$classfile;
						break;
					} else
						if (startsWith($buffer, "abstract class ")) {
							$parts = explode(" ", $buffer);
							$extdata['classes'][$parts[2]] = $path.$classfile;
							break;
						}
				}
				fclose($handle);
			} else
				throw new IOException($classfile, IOException::ReadAccess);
		}

		if (isset($infoData['condition']))
			$extdata['condition'] = $infoData['condition'];

		if (isset($infoData['database'])) {
			// Initialize Database as System User for User::getID() creation and modification attributes
			User::runAsSystem("Database::getInstance", Array($infoData['name'], $infoData['database']));
			$extdata['database'] = Array(
				"name" => $infoData['name'],
				"def" => $infoData['database']
			);
		}

		if (is_dir("pages")) {
			$extdata['page-path'] = Framework::splitPath(isset($infoData['page-root']) ? $infoData['page-root'] : $provides);
		}

		print_r($extdata);
		$extdata['infofile'] = self::$enabledFeatures[$provides]['infofile'];
		$extassoc[$provides] = $extdata;
	}

	protected function update() {
		$extdata = Array(
			"e" => Array(),
			"a" => Array()
		);

		$enabled = json_decode(file_get_contents($this->getFilepath()));
		foreach ($enabled as $extension) {
			try {
				if (!is_dir(($path = EXT_PATH.$extension.DIRSEP))) {
					$path = false;
					$dh  = opendir(FRAMEWORK_EXT_PATH);
					while (false !== ($path = readdir($dh))) {
						$path = FRAMEWORK_EXT_PATH . $path . DIRSEP . $extension . DIRSEP;
						if(is_dir($path))
							break;
						$path = false;
					}
				}
				if($path) {
					$infofile = $path."info.json";
					if (!file_exists($infofile))
						throw new IOException($path."info.json", IOException::NotFound);
					$infoData = json_decode(file_get_contents($infofile), true);
					if (!$infoData)
						throw new IOException($path."info.json", IOException::Corrupt);
					if ($extension != $infoData['name'])
						throw new Exception("Name Mismatch");

					self::$enabled[$extension] = $infoData['provides'];
					self::$enabledFeatures[$infoData['provides']] = Array("infofile" => $infofile, "path" => $path, "info" => $infoData);
				} else
					throw new Exception("Missing Extension `$extension`");
			} catch (Exception $e) {
				array_push($extdata['e'], new Exception("Failed to Process `$extension`", 0, $e));
			}
		}

		foreach (self::$enabledFeatures as $feature => $data) {
			try {
				self::readExtension($feature, $extdata['a']);
			} catch (Exception $e) {
				array_push($extdata['e'], $e);
			}
		}

		return $extdata;
	}

	private function _loadAll() {
		if (DEBUG_MODE)
			Profiler::start("ExtensionLoader");
		foreach ($this->getValue('a') as $provides => $extension) {
			if (DEBUG_MODE)
				Profiler::start("ExtensionLoader[$provides]");
			if (isset($extension['deps'])) {
				$skip = false;
				foreach ($extension['deps'] as $dep) {
					if (!self::isFeatureProvided($dep)) {
						$skip = true;
						continue;
					}
				}
				if ($skip)
					continue;
			}

			if (!chdir($extension['path']))
				throw new Exception("Failed to CHDIR to `".$extension['path']."`\n".print_r($extension, true));
			if (isset($extension['condition']) && !eval("return $extension[condition];"))
				continue;

			if (isset($extension['database']))
				Database::registerInstance($extension['database']['name'], $extension['path']);

			if (isset($extension['api']))
				self::$apis = array_merge(self::$apis, $extension['api']);

			if (isset($extension['page-path'])) {
				$pathStart =& self::$vpaths;

				foreach ($extension['page-path'] as $part) {
					if (!isset($pathStart['v'][$part]))
						$pathStart['v'][$part] = Array("p" => Array(), "v" => Array());
					$pathStart =& $pathStart['v'][$part];
				}

				array_push($pathStart['p'], fullpath("pages").DIRSEP);
			}

			if (isset($extension['classes']))
				ClassLoader::registerClasses($extension['classes']);

			if (DEBUG_MODE || DEV_MODE || isset($extension['bind-res-path']))
				Framework::addResourcePath("extensions/" . $provides . "/", $extension['path']);

			try {
				foreach ($extension['includes'] as $file) {
					$file = fullpath($file);
					if (!is_file($file))
						throw new Exception("$file is Missing, while loading $provides, ".$extension['path']);
					require $file;
				}
			} catch(Exception $e) {
				Framework::removeResourcePath("extensions/" . $provides . "/", $extension['path']);
				throw $e;
			}

			self::$extensionInfoFiles[$extension['name']] = $extension['infofile'];
			self::$loadedFeatures[$provides] = true;
			self::$loadedExtensions[$extension['name']] = true;
			if (DEBUG_MODE)
				Profiler::finish("ExtensionLoader[$provides]");
		}

		chdir(INDEX_PATH);
		if (DEBUG_MODE)
			Profiler::finish("ExtensionLoader");
	}

}
?>
