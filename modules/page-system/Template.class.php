<?php
class Template {

	const Header = 0x0;
	const BodyHeader = 0x1;
	const BodyFooter = 0x2;

	const NoColumns = 0x0;
	const LeftColumn = 0x1;
	const RightColumn = 0x2;
	const DualColumns = 0x3;

	private static $htmlDocument;
	private static $htmlHeader;
	private static $htmlBody;
	private static $title = DEFAULT_PAGE_NAME;
	private static $scripts = Array();
	private static $styles = Array();
	private static $styleMedia = Array();
	private static $inlineStyles = Array();
	private static $inlineStyleMedia = Array();
	private static $elements = Array(Array(), Array(), Array());
	private static $headerScripts = Array();
	private static $footerScripts = Array();
	private static $titleFormat = TITLE_FORMAT;
	private static $systemStyleMedia = Array();
	private static $globalScripts = Array();
	private static $globalStyles = Array();
	private static $systemStyles = Array();
	private static $nameSpaces = Array();
	private static $htmlAttrs = Array();
	private static $bodyAttrs = Array();

	public static function reset() {
		self::$title = DEFAULT_PAGE_NAME;
		self::$scripts = Array();
		self::$styles = Array();
		self::$styleMedia = Array();
		self::$elements = Array(Array(), Array(), Array());
		self::$headerScripts = Array();
		self::$footerScripts = Array();
		self::$systemStyleMedia = Array();
		self::$systemStyles = Array();
		self::$nameSpaces = Array();
		self::$htmlAttrs = Array();
		self::$bodyAttrs = Array();
		self::init();
	}

	public static function setTitleFormat($format) {
		self::$titleFormat = $format;
	}

	public static function init() {
		if (defined("META_KEYWORDS"))
			Template::setMetaTag("keywords", META_KEYWORDS);
		if (defined("META_DESCRIPTION"))
			Template::setMetaTag("description", META_DESCRIPTION);

		Template::setMetaTag("generator", "NexusFramework ".FRAMEWORK_VERSION." - OpenSource WebFramework");
		self::setRobotsPolicy(!defined("NO_ROBOTS"));

		self::addNameSpace("framework", "http://framework.nexustools.net/ns#");

		try {
			if (defined("LANGCODE"))
				$lang = LANGCODE;
			else {
				if (!class_exists("Locale", false))
					throw new Exception("Locale class not found");

				$locale = Locale::parseLocale(Locale::getDefault());
				$lang = $locale['language'];
				if ($locale['region'])
					$lang .= "-".$locale['region'];
			}
		} catch (Exception $e) {
			$lang = "en";
		}
		self::setHTMLAttr("lang", $lang);

		if (is_file($favicon = fullpath("favicon.ico")))
			self::setFavicon($favicon);
		else
			if (is_file($favicon = fullpath("favicon.png")))
				self::setFavicon($favicon);
			else
				if (is_file($favicon = fullpath("favicon.jpg")))
					self::setFavicon($favicon);
				else
					if (is_file($favicon = fullpath("favicon.jpeg")))
						self::setFavicon($favicon);
					else
						if (is_file($favicon = fullpath("favicon.gif")))
							self::setFavicon($favicon);
	}

	public static function addNameSpace($name, $url) {
		self::$nameSpaces[$name] = $url;
	}

	public static function setHTMLAttr($attr, $value) {
		self::$htmlAttrs[$attr] = $value;
	}

	public static function removeHTMLAttr($attr) {
		unset(self::$htmlAttrs[$attr]);
	}

	public static function setBodyAttr($attr, $value) {
		self::$bodyAttrs[$attr] = $value;
	}

	public static function removeBodyAttr($attr) {
		unset(self::$bodyAttrs[$attr]);
	}

	public static function setFavicon($path, $mime = null) {
		if ($mime === false)
			Template::addHeaderElement("link", Array("href" => $path, "rel" => "shortcut icon"));
		else
			Template::addHeaderElement("link", Array("href" => Framework::getReferenceURI($path),
				"rel" => "shortcut icon", "type" => $mime ? $mime : Framework::mimeForFile($path)));
	}

	public static function setRobotsPolicy($index, $follow = null) {
		if (!is_bool($follow))
			$follow = $index;
		Template::setMetaTag("robots", ($index ? "index" : "noindex").", ".($index ? "follow" : "nofollow"));
	}

	public static function addHeader($script) {
		if (is_string($script) && file_exists(fullpath($script))) {
			$id = Framework::uniqueHash($script);
			$script = Array(new PHPInclude($script), "run");
		} else
			if (is_array($script) && is_callable($script))
				$id = Framework::uniqueHash($script);
			else
				throw new Exception("Headers must be scripts or callable");

		if (!isset(self::$headerScripts[$id]))
			self::$headerScripts[$id] = $script;
	}

	public static function addFooter($script) {
		if (is_string($script) && file_exists($fpath = fullpath($script))) {
			$id = Framework::uniqueHash($fpath);
			$script = Array(new PHPInclude($fpath), "run");
		} else
			if (is_array($script) && is_callable($script))
				$id = Framework::uniqueHash($script);
			else
				throw new Exception("Footers must be scripts or callable");

		if (!isset(self::$footerScripts[$id]))
			self::$footerScripts[$id] = $script;
	}

	public static function addExternalScript($script) {
		$id = Framework::uniqueHash($script);
		if (!isset(self::$scripts[$id]))
			self::$scripts[$id] = $script;
	}

	public static function addScript($script) {
		$script = fullpath($script);
		if (!is_file($script))
			throw new Exception("Reference to Invalid File");
		$id = Framework::uniqueHash($script);
		if(isset(self::$globalScripts[$id]))
			return; // Already included globally
		
		if (!isset(self::$scripts[$id])) {
			self::$scripts[$id] = new CompressedScript($script);
			self::$scripts[$id] = self::$scripts[$id]->getReferenceURI();
		}
	}

	public static function addGlobalScript($script) {
		$script = fullpath($script);
		if (!is_file($script))
			throw new Exception("Reference to Invalid File");
		$id = Framework::uniqueHash($script);
		if(isset(self::$scripts[$id]))
			unset(self::$scripts[$id]);
		
		if (!isset(self::$globalScripts[$id])) {
			self::$globalScripts[$id] = new CompressedScript($script);
			self::$globalScripts[$id] = self::$globalScripts[$id]->getReferenceURI();
		}
	}

	public static function addGlobalStyle($style) {
		$style = fullpath($style);
		if (!is_file($style))
			throw new Exception("Reference to Invalid File");
		$id = Framework::uniqueHash($style);
		if(isset(self::$styles[$id]))
			unset(self::$styles[$id]);
		
		if (!isset(self::$globalStyles[$id])) {
			self::$globalStyles[$id] = new CompressedStyle($style);
			self::$globalStyles[$id] = self::$globalStyles[$id]->getReferenceURI();
		}
	}

	public static function addScripts($scripts) {
		if(DEBUG_MODE || DEV_MODE) {
			foreach($scripts as $script)
				self::addScript($script);
			return;
		}
		$compressedScripts = new ScriptCompressor();
		foreach ($scripts as $script)
			$compressedScripts->addScript(fullpath($script));
		if (!isset(self::$scripts[$compressedScripts->getID()]))
			self::$scripts[$compressedScripts->getID()] = $compressedScripts;
	}

	public static function importPrototypeAddon($script, $global =false) {
		$script = FRAMEWORK_RES_PATH . "javascript/addons/".StringFormat::idForDisplay($script).".js";
		if($global)
			self::addGlobalScript($script);
		else
			self::addScript($script);
	}

	public static function addExternalStyle($style, $media = false) {
		$id = Framework::uniqueHash($style);
		if (!isset(self::$styles[$id]))
			self::$styles[$id] = $style;
		if ($media)
			self::$styleMedia[$id] = $media;
	}

	public static function clearSystemStyles() {
		self::$systemStyles = Array();
		self::$systemStyleMedia = Array();
	}

	public static function addSystemStyle($style, $media = false) {
		$style = fullpath($style);
		$id = Framework::uniqueHash($style);
		if (!isset(self::$systemStyles[$id]))
			self::$systemStyles[$id] = new CompressedStyle($style);
		if ($media)
			self::$systemStyleMedia[$id] = $media;
	}

	public static function addInlineStyle($style, $media = false) {
		if(!($style = trim($style)))
			return;
		if(!DEV_MODE && !DEBUG_MODE)
			$style = StyleCompressor::compress($style);
	
		$id = Framework::uniqueHash($style);
		if (!isset(self::$inlineStyles[$id]))
			self::$inlineStyles[$id] = $style;
		if ($media)
			self::$inlineStyleMedia[$id] = $media;
	}

	public static function addStyle($style, $media = false) {
		$style = fullpath($style);
		$id = Framework::uniqueHash($style);
		if(isset(self::$globalStyles[$id]))
			return; // Already included globally
		
		if (!isset(self::$styles[$id]))
			self::$styles[$id] = new CompressedStyle($style);
		if ($media)
			self::$styleMedia[$id] = $media;
	}

	public static function addStyles($styles, $media = false) {
		if(DEBUG_MODE || DEV_MODE) {
			foreach($styles as $style)
				self::addStyle($style);
			return;
		}
		$compressedStyles = new StyleCompressor();
		foreach ($styles as $style)
			$compressedStyles->addStyle(fullpath($style));
		if (!isset(self::$styles[$compressedStyles->getID()]))
			self::$styles[$compressedStyles->getID()] = $compressedStyles;
		if ($media)
			self::$styleMedia[$compressedStyles->getID()] = $media;
	}

	public static function addElement($level, $tag, $attr, $content = false) {
		$element = Array("tag" => $tag, "attr" => $attr);
		if ($content)
			$element['content'] = $content;
		if (isset($attr['id']))
			self::$elements[$level][$attr['id']] = $element;
		else
			if (isset($attr['name']))
				self::$elements[$level]['name:'.$attr['name']] = $element;
			else
				if (isset($attr['rel']))
					self::$elements[$level]['rel:'.$attr['rel']] = $element;
				else
					array_push(self::$elements[$level], $element);
	}

	public static function addHeaderElement($tag, $attr, $content = false) {
		self::addElement(Template::Header, $tag, $attr, $content);
	}

	public static function setMetaTag($name, $value, $attr = "content") {
		self::addHeaderElement("meta", Array("name" => $name, $attr => $value));
	}

	public static function setTitle($title) {
		self::$title = $title;
	}

	public static function getTitle() {
		return self::$title;
	}

	public static function writeHeader() {
		if (DEBUG_MODE)
			Profiler::start("Template[Header]");
		Triggers::broadcast("template", "pre-header");
		echo "<!DOCTYPE html>\n<html";
		foreach (self::$nameSpaces as $name => $url)
			echo " prefix=\"$name: $url\"";
		foreach (self::$htmlAttrs as $key => $val)
			echo " $key=\"".htmlspecialchars($val)."\"";
		echo "><head><meta charset=\"UTF-8\"><base href=\"";
		echo BASE_URL;
		echo "\" /><title>";
		echo interpolate(self::$titleFormat, true, Array("PAGENAME" => self::$title));
		echo "</title><!--[if lt IE 9]>
<script src=\"http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js\"></script>
<![endif]--><style type=\"text/css\">Framework\:Config,Framework\:AddonScript {display:none}</style>";
		foreach (self::$elements[Template::Header] as $data) {
			echo "<$data[tag]";
			foreach ($data['attr'] as $key => $value) {
				echo " $key=\"";
				echo htmlspecialchars($value);
				echo "\"";
			}

			if (array_key_exists("content", $data)) {
				echo ">$data[content]</$data[tag]>";
			} else
				echo " />";
		}

		if(DEV_MODE || DEBUG_MODE)
			echo "<!-- Begin GlobalStyles -->";
		foreach (self::$globalStyles as $id => $style) {
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"";
			if ($style instanceof StyleCompressor) {
				echo $style->getReferenceURI("text/css");
				if (DEBUG_MODE)
					echo "\" storage=\"".$style->getStoragePath();
			} else
				echo "$style";
			echo "\" />";
		}
		if(DEV_MODE || DEBUG_MODE)
			echo "<!-- End GlobalStyles --><!-- Begin SystemStyles -->";
		foreach (self::$systemStyles as $id => $style) {
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"";
			if ($style instanceof CachedObject)
				echo $style->getReferenceURI("text/css");
			else
				echo "$style";
			if (isset(self::$systemStyleMedia[$id]))
				echo "\" media=\"".self::$systemStyleMedia[$id];
			echo "\" />";
		}
		if(DEV_MODE || DEBUG_MODE)
			echo "<!-- End SystemStyles --><!-- Begin Styles -->";
		foreach (self::$styles as $id => $style) {
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"";
			if ($style instanceof CachedObject)
				echo $style->getReferenceURI("text/css");
			else
				echo "$style";
			if (isset(self::$styleMedia[$id]))
				echo "\" media=\"".self::$styleMedia[$id];
			echo "\" />";
		}
		if(DEV_MODE || DEBUG_MODE)
			echo "<!-- End Styles --><!-- Begin InlineStyles -->";
		foreach (self::$inlineStyles as $id => $style) {
			echo "<style type=\"text/css\"";
			if (isset(self::$inlineStyleMedia[$id]))
				echo " media=\"".self::$inlineStyleMedia[$id];
			echo ">$style</style>";
		}
		if(DEV_MODE || DEBUG_MODE)
			echo "<!-- End InlineStyles -->";
		Triggers::broadcast("template", "header");
		echo "</head><body";
		foreach (self::$bodyAttrs as $key => $val)
			echo " $key=\"".htmlspecialchars($val)."\"";
		echo ">";
		Triggers::broadcast("template", "body-header");
		foreach (self::$headerScripts as $script)
			call_user_func($script);

		if (DEBUG_MODE)
			Profiler::finish("Template[Header]");
	}

	public static function writeFooter() {
		if (DEBUG_MODE)
			Profiler::start("Template[Footer]");
		Triggers::broadcast("template", "body-footer");

		foreach (self::$footerScripts as $callable)
			call_user_func($callable);

		$fm_config = Triggers::broadcast("template", "config");

		$fm_config = json_encode(array_merge_recursive($fm_config,
			Array("TITLE_FORMAT" => self::$titleFormat,
				"DEFAULT_PAGE_NAME" => DEFAULT_PAGE_NAME,
				"FRAMEWORK_VERSION" => FRAMEWORK_VERSION,
				"LEGACY_BROWSER" => LEGACY_BROWSER)));
		echo "<framework:config version=\"";
		echo Framework::uniqueHash($fm_config, Framework::URLSafeHash);
		echo "\"><!-- ($fm_config) --></framework:config>";
		
		$compress = !DEBUG_MODE && !DEV_MODE;
		if(!$compress)
			echo "<!-- Start NexusFrameworkLibs -->";
		$path = FRAMEWORK_RES_PATH . "javascript" . DIRSEP . "libs" . DIRSEP;
		foreach(glob("$path*", GLOB_MARK | GLOB_ONLYDIR) as $libPath) {
			$script = false;
			foreach(glob("$libPath*.js") as $libFile) {
				if(!is_file($libFile) || !is_readable($libFile))
					continue;
				
				if($compress) {
					if(!$script)
						$script = new ScriptCompressor(true);
					
					$script->addScript($libFile);
				} else {
					$script = new CompressedScript($libFile);
					
					echo "<script src=\"";
					echo $script->getReferenceURI("text/javascript");
					echo "\"></script>";
					
					$script = false;
				}
			}
			
			if($script) {
				echo "<script src=\"";
				echo $script->getReferenceURI("text/javascript");
				echo "\"></script>";
			}
		}
		if(!$compress)
			echo "<!-- End NexusFrameworkLibs --><!-- Begin GlobalScripts -->";

		foreach (self::$globalScripts as $id => $script) {
			echo "<script src=\"";
			if ($script instanceof ScriptCompressor) {
				echo $script->getReferenceURI("text/javascript");
			} else
				echo "$script";
			echo "\"></script>";
		}
		if(!$compress)
			echo "<!-- End GlobalScripts --><!-- Begin Scripts -->";

		foreach (self::$scripts as $id => $script) {
			echo "<script src=\"";
			if ($script instanceof ScriptCompressor) {
				echo $script->getReferenceURI("text/javascript");
				if (DEBUG_MODE)
					echo "\" storage=\"".$script->getStoragePath();
			} else
				echo "$script";
			echo "\"></script>";
		}

		if (DEBUG_MODE) {
			Profiler::finish("Template[Footer]");
			Profiler::finish("Template");

			echo "<center><div style=\"clear: both; margin-top: 80px; padding: 5px; background-color: black; background-color: rgba(0, 0, 0, 0.7); color: white; font-size: 10px;\"><a style=\"font-size: 12px; color: white; font-weight: bold;\" href=\"";
			echo REQUEST_URL;
			echo "?dumpstate\">Debug Information</a><br />";
			echo CachedObject::countInstances()." Cached Objects Loaded<br />";
			echo ClassLoader::countLoadedClasses()." Classes Loaded<br />";
			echo Database::countQueries()." SQL Queries<br /><br />";
			echo "<span style=\"font-size: 11px; font-weight: bold;\">Profiler</span>";
			$labels = "";
			$data = "";
			foreach (Profiler::getTimers() as $key => $value) {
				if ($value < 1 || $key == "Total")
					continue;
				if ($labels)
					$labels .= "|";
				$labels .= urlencode($key);
				if ($data)
					$data .= ",";
				$data .= urlencode($value);
			}
			echo "<br /><img src=\"http://chart.apis.google.com/chart?cht=p3&chd=t:$data&chs=800x150&chl=$labels&chma=80,80,5,5&chf=bg,s,00000000\" />";
			echo "<table style=\"border: solid 1pt rgba(0, 0, 0, 0.5)\"";
			echo " cellpadding=\"2\" cellspacing=\"0\" style=\"font-size: 10px;\">";
			echo "<tr style=\"background-color: rgba(0, 0, 0, 0.5)\">";
			echo "<th align=\"left\">Section</th>";
			echo "<th align=\"right\">MS</th></tr>";
			$alt = false;
			foreach (Profiler::getTimers() as $key => $value) {
				echo "<tr";
				if ($alt)
					echo " style=\"background-color: rgba(0, 0, 0, 0.2)\"";
				echo "><td style=\"padding-right: 20px;\">$key</td>";
				echo "<td align=\"right\">$value</td></tr>";
				$alt = !$alt;
			}
			echo "<tr style=\"background-color: rgba(0, 0, 0, 0.5)\">";
			echo "<td>Total</td><td>";
			echo number_format((microtime(true) - LOADER_START_TIME) * 1000, 2);
			echo "</td></tr></table></center></div>";
		}
		echo "</body></html>";
	}

}

function requireAddon($script, $global =false) {
	Template::importPrototypeAddon($script, $global);
}

Template::init();
?>
