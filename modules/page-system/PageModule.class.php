<?php
class PageModule {

	private static $instance;
	private $workingPath;
	private $arguments;
	private $globals;
	private $theme;
	private $error = false;
	private $headscript = false;
	private $script = false;
	private $sidebarLayout;
	private $rightSidebarScript;
	private $leftSidebarScript;
	private $themePath;
	private $pageTitle = false;
	private $pageDescription = false;
	private $pageKeywords = false;
	private $realUri = false;
	private $buffer = false;
	private $badCond = false;
	private $get;
	private $post;

	const RAW_SIDEBAR_OUTPUT = false;
	const NO_SIDEBARS = 0;
	const LEFT_SIDEBAR = 1;
	const RIGHT_SIDEBAR = 2;
	const BOTH_SIDEBARS = 3;

	public function getHTML($pageAreaOnly = false) {
		if (!is_string($this->buffer))
			$this->run(true, $pageAreaOnly);
		return $this->buffer;
	}

	public function _getWorkingPath() {
		return $this->workingPath;
	}

	public static function pathExists($path) {
		new PageModule($path);
	}

	public function _setValue($key, $val) {
		$this->globals[$key] = $val;
	}

	public function _hasValue($key) {
		return isset($this->globals[$key]) &&
				$this->globals[$key] !== null;
	}

	public function _getValue($key) {
		$data = $this->globals;
		foreach (func_get_args() as $arg) {
			if (!array_key_exists($arg, $data))
				return false;
			$data = $data[$arg];
		}

		return $data;
	}

	public function _countArguments() {
		return count($this->arguments);
	}

	public function _getArgument($index) {
		return $this->arguments[$index];
	}

	public function _getArguments() {
		return $this->arguments;
	}

	private function _getError() {
		return $this->error;
	}

	private function setError($status) {
		switch ($status) {
		case 403:
			$this->error = Array("code" => 403,
				"message" => "Permission Denied",
				"body" => "<p>You do not have the permission required to view this page.</p>");
			break;

		case 404:
			$this->error = Array("code" => 404,
				"message" => "Page Not Found",
				"body" => "<p>The page your trying to visit does not exist.</p>");
			break;

		case 500:
			if (defined("ERROR_HIDE_DETAILS")) {
				$details = "<br /><br /><b>Reference Code</b><br />".framework_get_error_hash();
			} else {
				$details = framework_get_error_details();

				if ($details) {
					$details = '<br /><br /><a onclick="$(this).hide();$(this).next(\'div\').show();" style="cursor: pointer; font-size: 95%">Show More Details</a><div style="display: none"><pre style="margin: 0 auto; width: auto; display: table; text-align: left;">'.print_r($details, true).'</pre></div>';
				} else
					$details = false;
			}

			$errorType = framework_get_error_type();
			$this->error = Array("code" => 500,
				"message" => "Internal Error",
				"body" => "<p>An error occured while processing your request.<br />It is not possible to recover from this error.<br /><br />Our technicians have been notified and will look into it as soon as possible.<br />Sorry for any Inconvenience.</p><pre style='margin-top: 34px; font-size: 75%'><b>Unhandled $errorType Thrown</b><br />".framework_get_error_message()."$details</pre>");
			break;

		default:
			$this->error = Array("code" => $status,
				"message" => "Unhandled Status Code",
				"body" => "<p>So apparently you've decided to start entering random status codes,<br />well here's a list of <a href=\"http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html\">HTTP Status Codes</a>.<br /><br />If you didn't mean to get this page, please contact the administrator to report this.</p>");
			break;
		}

		$this->pageTitle = $this->error['message'];
	}

	private function exploreVirtualPaths($arguments, $pureVirtualPaths) {
		if (!isset($pureVirtualPaths['p']) || $this->script)
			return true;

		foreach ($pureVirtualPaths['p'] as $path) {
			if ($this->findModule($path, $arguments))
				return true;
		}

		if (!$this->script && isset($pureVirtualPaths['v']) && count($arguments) > 0) {
			$path = array_shift($arguments);
			if (isset($pureVirtualPaths['v'][$path]))
				return $this->exploreVirtualPaths($arguments, $pureVirtualPaths['v'][$path]);
		}

		return false;

	}

	public function _hasError() {
		return $this->error !== false;
	}

	public function _getPageTitle() {
		return $this->pageTitle;
	}

	public function __construct($path, $pureVirtualPaths = false, $ignorePrepend = false) {
		global $__framework_activePath;
		if (DEBUG_MODE) {
			Profiler::start("PageModule");
			Profiler::start("PageModule[Constructor]");
		}
		$oldInstance = self::$instance;
		self::$instance = $this;

		$this->get = $_GET;
		$this->post = $_POST;
		if (defined("THEME"))
			$this->themePath = "themes".DIRSEP.THEME.DIRSEP;
		else
			$this->themePath = FRAMEWORK_PATH."media-theme".DIRSEP;

		$this->globals = Array();
		if ((!LEGACY_OS && startsWith($path, "about:")) || (LEGACY_OS && startsWith($path, "about-"))) {
			$this->workingPath = cleanpath("/$path");
			$this->arguments = Framework::splitPath(substr($path, 6));
			$this->findModule(FRAMEWORK_PATH."resources".DIRSEP."about".DIRSEP, $this->arguments);
		} else {
			if (!defined("PATH_PREPEND") || $ignorePrepend)
				$this->workingPath = cleanpath("/$path");
			else
				$this->workingPath = cleanpath("/".PATH_PREPEND."/$path");
			$this->arguments = Framework::splitPath($this->workingPath);

			if (count($this->arguments) == 2 && $this->arguments[0] == "errordoc" && is_numeric($this->arguments[1]))
				$this->setError(intval($this->arguments[1]));
			else {
				if (!isset($__framework_activePath))
					$__framework_activePath = INDEX_PATH;

				if (!$this->findModule($__framework_activePath."pages/", $this->arguments)) {
					if ($pureVirtualPaths === false)
						$pureVirtualPaths = ExtensionLoader::getVirtualPaths();
					$this->exploreVirtualPaths($this->arguments, $pureVirtualPaths);
				}
			}
		}

		if (!$this->script && !$this->error)
			$this->setError($this->badCond ? BAD_CONDITION_STATUS : 404);

		if ($this->error !== false) {
			$this->workingPath = "/errordoc/".$this->error['code'];
			$this->arguments = Array("errordoc", $this->error['code']);

			if (!$this->findModule(INDEX_PATH."pages/", $this->arguments)) {
				if ($pureVirtualPaths === false)
					$pureVirtualPaths = ExtensionLoader::getVirtualPaths();

				if (!$this->exploreVirtualPaths($this->arguments, $pureVirtualPaths)) {
					//header("Content-Type: text/plain");
					//OutputHandlerStack::stop();
					$base = INDEX_PATH."pages".DIRSEP;
					$sidebarPaths = Array("errordoc/root", "errordoc", "root");

					foreach ($sidebarPaths as $path) {
						if ($this->sidebarLayout === false && is_file("$base$path.sidebars"))
							$this->sidebarLayout = trim(file_get_contents("$base$path.sidebars")) * 1;

						if (!$this->leftSidebarScript && is_file("$base$path.sidebar.left.inc.php"))
							$this->leftSidebarScript = "$base$path.sidebar.left.inc.php";
						if (!$this->rightSidebarScript && is_file("$base$path.sidebar.right.inc.php"))
							$this->rightSidebarScript = "$base$path.sidebar.right.inc.php";
					}
					//die();
				}
			}
		}

		if (DEBUG_MODE)
			Profiler::finish("PageModule[Constructor]");
		
		self::$instance = $oldInstance;
	}

	public static function setThemePath($path) {
		self::$instance->themePath = fullpath($path);
	}

	public function initialize($real = true) {
		if($real) {
			if (DEBUG_MODE)
				Profiler::start("PageModule[Initialize]");
			$_GET = $this->get;
			$_POST = $this->post;
		} else
			$oldInstance = self::$instance;
		
		self::$instance = $this;
		if ($this->pageTitle)
			Template::setTitle($this->pageTitle);
		if ($this->pageDescription)
			Template::setMetaTag("description", $this->pageDescription);
		if ($this->pageKeywords)
			Template::setMetaTag("keywords", $this->pageKeywords);

		if ($this->headscript)
			require $this->headscript;

		$this->initializeTheme(false);

		if($real) {
			if ($this->error)
				header("http/1.1 ".$this->error['code']." ".$this->error['message']);

			if (DEBUG_MODE) {
				Profiler::finish("PageModule[Initialize]");
				Profiler::finish("PageModule");
			}
		} else
			self::$instance = $oldInstance;
	}
	
	public function initializeTheme($fully =true) {
		$this->theme = Theme::getInstanceByPath($this->themePath);
		
		if($fully)
			$this->theme->initialize();
	}

	private function findModule($basepath, $parts, $resetFindings = true) {
		if ($resetFindings) {
			$this->sidebarLayout = false;
			$this->rightSidebarScript = false;
			$this->leftSidebarScript = false;
		}

		$_GET = $this->get;
		$_POST = $this->post;
		$mname = count($parts) > 0 ? array_shift($parts) : "root";
		if (is_file($basepath."root.exists.inc.php")) {
			$ret = require($basepath."root.exists.inc.php");
			if ($ret === false)
				return false;
			else
				if (is_string($ret)) {
					if (startsWith($ret, "/"))
						return PageModule::findModule("$ret/", $parts, false);
					else
						return PageModule::findModule("$basepath".relativepath($ret)."/", $parts, false);
				}
		}
		if (is_file($basepath."root.cond.inc.php") && !require($basepath."root.cond.inc.php")) {
			$this->badCond = true;
			return false;
		}

		if (is_file($basepath."root.sidebars"))
			$this->sidebarLayout = trim(file_get_contents($basepath."root.sidebars")) * 1;
		if (is_file($basepath."root.sidebar.left.inc.php"))
			$this->leftSidebarScript = $basepath."root.sidebar.left.inc.php";
		if (is_file($basepath."root.sidebar.right.inc.php"))
			$this->rightSidebarScript = $basepath."root.sidebar.right.inc.php";

		if (is_file($basepath."root.desc"))
			$this->pageDescription = trim(file_get_contents($basepath."root.desc"));
		else
			if (is_file($basepath."root.description"))
				$this->pageDescription = trim(file_get_contents($basepath."root.description"));

		if (is_file($basepath."root.tags"))
			$this->pageKeywords = trim(file_get_contents($basepath."root.tags"));
		else
			if (is_file($basepath."root.keywords"))
				$this->pageKeywords = trim(file_get_contents($basepath."root.keywords"));

		$dirExists = null;
		if (count($parts)) {
			if (is_dir("$basepath$mname"))
				return $this->findModule("$basepath$mname/", $parts, false);
		} else {
			if ($mname != "root" && is_dir("$basepath$mname"))
				return $this->findModule("$basepath$mname/", $parts, false);

			if (is_file("$basepath$mname.redirect"))
				Framework::redirect(file_get_contents(fullpath("$basepath$mname.redirect")));
			else
				if (is_file("$basepath$mname.inc.php")) {
					if (is_file("$basepath$mname.virt.inc.php")) {
						$ret = require("$basepath$mname.virt.inc.php");

						if (!$ret || $ret == null)
							return false;
						else
							if (is_numeric($ret)) {
								if ($ret >= 100 && $ret <= 500) {
									$this->setError($ret);
									return false;
								} else
									return false;
							}
					} else
						if (count($parts))
							return false;

					if ($mname != "root") {
						if (is_file("$basepath$mname.sidebars"))
							$this->sidebarLayout = trim(file_get_contents("$basepath$mname.sidebars")) * 1;
						if (is_file("$basepath$mname.sidebar.left.inc.php"))
							$this->leftSidebarScript = "$basepath$mname.sidebar.left.inc.php";
						if (is_file("$basepath$mname.sidebar.right.inc.php"))
							$this->rightSidebarScript = "$basepath$mname.sidebar.right.inc.php";
						if (is_file("$basepath$mname.cond.inc.php") && !require("$basepath$mname.cond.inc.php")) {
							$this->badCond = true;
							return false;
						}

						if (is_file("$basepath$mname.desc"))
							$this->pageDescription = trim(file_get_contents("$basepath$mname.desc"));
						else
							if (is_file("$basepath$mname.description"))
								$this->pageDescription = trim(file_get_contents("$basepath$mname.description"));

						if (is_file("$basepath$mname.tags"))
							$this->pageKeywords = trim(file_get_contents("$basepath$mname.tags"));
						else
							if (is_file("$basepath$mname.keywords"))
								$this->pageKeywords = trim(file_get_contents("$basepath$mname.keywords"));
					}

					if (is_file("$basepath$mname.title"))
						$this->pageTitle = trim(file_get_contents("$basepath$mname.title"));

					if (is_file("$basepath$mname.head.inc.php"))
						$this->headscript = fullpath("$basepath$mname.head.inc.php");

					$this->script = fullpath("$basepath$mname.inc.php");

					$this->get = $_GET;
					$this->post = $_POST;
					return true;
				}
		}

		if (is_file($basepath."virtual.exists.inc.php")) {
			$ret = require($basepath."virtual.exists.inc.php");

			if (!$ret || $ret == null)
				return false;
			else
				if (is_numeric($ret) && $ret != 200) {
					if ($ret >= 100 && $ret <= 500) {
						$this->setError($ret);
						return false;
					} else
						return false;
				} else
					if (is_string($ret)) {
						if (startsWith($ret, "/"))
							return PageModule::findModule("$ret/", $parts, false);
						else
							return PageModule::findModule("$basepath".relativepath($ret)."/", $parts, false);
					}

			if (is_file($basepath."virtual.cond.inc.php") && !require($basepath."virtual.cond.inc.php")) {
				$this->badCond = true;
				return false;
			}

			if (is_file($basepath."virtual.head.inc.php"))
				$this->headscript = $basepath."virtual.head.inc.php";

			$this->script = $basepath."virtual.inc.php";
			if (!is_file($this->script))
				throw new IOException($this->script, IOException::NotFound, "PageModule provides virtual existence script, but no page implementation.");

			$this->get = $_GET;
			$this->post = $_POST;
			return true;
		}

		return false;
	}

	public function run($capture = false, $onlyPageArea = false) {
		if (DEBUG_MODE)
			Profiler::start("PageModule[Script]");

		if ($capture || $onlyPageArea)
			$this->buffer = new OutputCapture();

		if (!$onlyPageArea) {
			if ($this->sidebarLayout !== self::RAW_SIDEBAR_OUTPUT) {
				if ($this->sidebarLayout == self::LEFT_SIDEBAR || $this->sidebarLayout == self::BOTH_SIDEBARS) {
					echo "<column class='sidebar left'>";
					require $this->leftSidebarScript;
					echo "</column>";
				}
				echo "<column class='pagearea";
				switch ($this->sidebarLayout) {
				case self::NO_SIDEBARS:
					echo " large";
					break;
				case self::LEFT_SIDEBAR:
				case self::RIGHT_SIDEBAR:
					echo " medium";
					break;
				case self::BOTH_SIDEBARS:
					echo " small";
					break;
				}
				echo "'>";
			}
		}
		if ($this->script)
			require $this->script;
		else {
			echo "<widget class='error error-";
			echo $this->error['code'];
			echo "'><h1>";
			echo $this->error['message'];
			echo "</h1><p>";
			echo $this->error['body'];
			echo "</p></widget>";
		}
		if (!$onlyPageArea && $this->sidebarLayout !== self::RAW_SIDEBAR_OUTPUT) {
			echo "</column>";

			if ($this->sidebarLayout == self::RIGHT_SIDEBAR || $this->sidebarLayout == self::BOTH_SIDEBARS) {
				echo "<column class='sidebar right'>";
				require $this->rightSidebarScript;
				echo "</column>";
			}
		}

		if ($capture || $onlyPageArea)
			$this->buffer = $this->buffer->finish();

		if ($onlyPageArea) {
			if ($this->sidebarLayout === self::RAW_SIDEBAR_OUTPUT && preg_match("/<column\s.*?class=['\"].*?pagearea.*?['\"].*?>((.|\n)*?)<\/column>/i", $this->buffer, $matches))
				$this->buffer = trim($matches[1]);
			if (!$capture)
				echo $this->buffer;
		}

		if (DEBUG_MODE)
			Profiler::finish("PageModule[Script]");
	}

	public function getTheme() {
		return $this->theme;
	}

	public function __toString() {
		$this->run(true);
		return $this->buffer;
	}

	public function __call($name, $args) {
		$method = false;
		try {
			$method = new ReflectionMethod($this, "_$name");
		} catch (Exception $e) {}

		if ($method)
			return $method->invokeArgs($this, $args);

		throw new Exception("Call to undefined method PageModule::$name()");
	}

	public static function __callStatic($name, $args) {
		if(!self::$instance)
			throw new Exception("No active PageModule to call `$name` on");
		return self::$instance->__call($name, $args);
	}

}
?>
