<?php
class Template {

	const Header      = 0x0;
	const BodyHeader  = 0x1;
	const BodyFooter  = 0x2;
	
	const NoColumns   = 0x0;
	const LeftColumn  = 0x1;
	const RightColumn = 0x2;
	const DualColumns = 0x3;

	private static $htmlDocument;
	private static $htmlHeader;
	private static $htmlBody;
	private static $title = DEFAULT_PAGE_NAME;
	private static $scripts = Array();
	private static $styles = Array();
	private static $styleMedia = Array();
	private static $elements = Array(Array(), Array(), Array());
	private static $headerScripts = Array();
	private static $footerScripts = Array();
	private static $titleFormat = TITLE_FORMAT;
	
	public static function reset(){
		self::$title = DEFAULT_PAGE_NAME;
		self::$scripts = Array();
		self::$styles = Array();
		self::$styleMedia = Array();
		self::$elements = Array(Array(), Array(), Array());
		self::$headerScripts = Array();
		self::$footerScripts = Array();
		self::init();
	}
	
	public static function setTitleFormat($format){
	    self::$titleFormat = $format;
	}
	
	public static function init(){
		if(defined("META_KEYWORDS"))
			Template::setMetaTag("keywords", META_KEYWORDS);
		if(defined("META_DESCRIPTION"))
			Template::setMetaTag("description", META_DESCRIPTION);

		Template::setMetaTag("generator", "NexusFramework " . FRAMEWORK_VERSION . " - OpenSource WebFramework");
		self::setRobotsPolicy(true);

		if(is_file($favicon = fullpath("favicon.ico")))
			self::setFavicon($favicon);
		else if(is_file($favicon = fullpath("favicon.png")))
			self::setFavicon($favicon);
		else if(is_file($favicon = fullpath("favicon.jpg")))
			self::setFavicon($favicon);
		else if(is_file($favicon = fullpath("favicon.jpeg")))
			self::setFavicon($favicon);
		else if(is_file($favicon = fullpath("favicon.gif")))
			self::setFavicon($favicon);
	}
	
	public static function setFavicon($path, $mime=null){
	    if($mime === false)
	        Template::addHeaderElement("link", Array("href" => $path, "name" => "favicon", "rel" => "shortcut icon"));
		else
		    Template::addHeaderElement("link", Array("href" => Framework::getReferenceURI($path), "name" => "favicon", "rel" => "shortcut icon", "type" => $mime ? $mime : Framework::mimeForFile($path)));
	}
	
	public static function setRobotsPolicy($index,  $follow=null){
		if(!is_bool($follow))
			$follow = $index;
		Template::setMetaTag("robots", ($index ? "index" : "noindex") . ", " . ($index ? "follow" : "nofollow"));
	}
	
	public static function addHeader($script){
	    if(is_string($script) && file_exists(fullpath($script))) {
	        $id = Framework::uniqueHash($script);
	        $script = Array(new PHPInclude($script), "run");
	    } else if(is_array($script) && is_callable($script))
	        $id = Framework::uniqueHash($script);
	    else 
	        throw new Exception("Headers must be scripts or callable");
	        
		if(!isset(self::$headerScripts[$id]))
			self::$headerScripts[$id] = $script;
	}
	
	public static function addFooter($script){
	    if(is_string($script) && file_exists(fullpath($script))) {
	        $id = Framework::uniqueHash($script);
	        $script = Array(new PHPInclude($script), "run");
	    } else if(is_array($script) && is_callable($script))
	        $id = Framework::uniqueHash($script);
	    else 
	        throw new Exception("Footers must be scripts or callable");
	        
		if(!isset(self::$footerScripts[$id]))
			self::$footerScripts[$id] = $script;
	}
	
	public static function addExternalScript($script){
		$id = Framework::uniqueHash($script);
		if(!isset(self::$scripts[$id]))
			self::$scripts[$id] = $script;
	}
	
	public static function addScript($script){
		$script = fullpath($script);
		if(!is_file($script))
		    throw new Exception("Reference to Invalid File");
		$id = Framework::uniqueHash($script);
		if(!isset(self::$scripts[$id])) {
			self::$scripts[$id] = new CompressedScript($script);
			self::$scripts[$id] = self::$scripts[$id]->getReferenceURI();
	    }
	}
	
	public static function addScripts($scripts){
		$compressedScripts = new ScriptCompressor();
		foreach($scripts as $script)
			$compressedScripts->addScript($script = fullpath($script));
		if(!isset(self::$styles[$compressedScripts->getID()]))
			self::$styles[$compressedScripts->getID()] = $compressedScripts;
	}
	
	public static function importPrototypeAddon($script){
		self::addScript("resources/javascript/addons/" . StringFormat::idForDisplay($script) . ".js");
	}
	
	public static function addStyle($style, $media="screen"){
		$style = fullpath($style);
		$id = Framework::uniqueHash($style);
		if(!isset(self::$styles[$id]))
			self::$styles[$id] = new CompressedStyle($style);
	    if($media)
	        self::$styleMedia[$id] = $media;
	}
	
	public static function addStyles($styles, $media="screen"){
		$compressedStyles = new StyleCompressor();
		foreach($styles as $style)
			$compressedStyles->addStyle($style = fullpath($style));
		if(!isset(self::$styles[$compressedStyles->getID()]))
			self::$styles[$compressedStyles->getID()] = $compressedStyles;
	    if($media)
	        self::$styleMedia[$compressedStyles->getID()] = $media;
	}
	
	public static function addElement($level, $tag, $attr, $content=false){
		$element = Array("tag" => $tag, "content" => $content, "attr" => $attr);
		if(isset($attr['id']))
			self::$elements[$level][$attr['id']] = $element;
		else if(isset($attr['name']))
			self::$elements[$level]['name:' . $attr['name']] = $element;
		else
			array_push(self::$elements[$level], $element);
	}
	
	public static function addHeaderElement($tag, $attr, $content=false){
		self::addElement(Template::Header, $tag, $attr, $content);
	}
	
	public static function setMetaTag($name, $value, $attr="content"){
		self::addHeaderElement("meta", Array("name" => $name, $attr => $value));
	}
	
	public static function setTitle($title){
		self::$title = $title;
	}
	
	public static function getTitle(){
		return self::$title;
	}
	
	public static function writeHeader(){
		if(DEBUG_MODE)
			Profiler::start("Template[Header]");
		Triggers::broadcast("template", "pre-header");
		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-gb\" lang=\"en-gb\" xmlns=\"framework\"><head><base href=\"";
		echo BASE_URL;
		echo "\" /><title>";
		echo interpolate(self::$titleFormat, true, Array("PAGENAME" => self::$title));
		echo "</title><style>Framework\:Config,Framework\:AddonScript {display:none}</style>";
		foreach(self::$elements[Template::Header] as $data){
			echo "<$data[tag]";
			foreach($data['attr'] as $key => $value)
				echo " $key=\"" . htmlspecialchars($value) . "\"";
			
			if($data['content']) {
				echo ">$data[content]</$data[tag]>";
			} else
				echo " />";
		}
		foreach(self::$styles as $id => $style){
		    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"";
		    echo $style->getReferenceURI("text/css");
		    if(DEBUG_MODE)
			    echo "\" storage=\"" . $style->getStoragePath();
			if(isset(self::$styleMedia[$id]))
			    echo "\" media=\"" . self::$styleMedia[$id];
		    echo "\" resource-id=\"$id\" />";
		}
		Triggers::broadcast("template", "header");
		echo "</head><body>";
		Triggers::broadcast("template", "body-header");
		foreach(self::$headerScripts as $script)
			call_user_func($script);
		
		if(DEBUG_MODE)
			Profiler::finish("Template[Header]");
	}
	
	public static function writeFooter(){
		if(DEBUG_MODE)
			Profiler::start("Template[Footer]");
		Triggers::broadcast("template", "body-footer");
		
		foreach(self::$footerScripts as $callable)
			call_user_func($callable);
			
		$fm_config = Triggers::broadcast("template", "config");
		$fm_config = json_encode(array_merge_recursive($fm_config, Array("TITLE_FORMAT" => self::$titleFormat,
								"DEFAULT_PAGE_NAME" => DEFAULT_PAGE_NAME,
								"BASE_URI" => BASE_URI,
								"BASE_URL" => BASE_URL)));
		echo "<framework:config version=\"";
		echo Framework::uniqueHash($fm_config, Framework::URLSafeHash);
		echo "\"><!-- ($fm_config) --></framework:config>";
		echo "<script resource-id=\"__framework-base__\" src=\"" . SHARED_RESOURCE_URL . "script\" language=\"javascript\"></script>";
		foreach(self::$scripts as $id => $script) {
			echo "<script resource-id=\"$id\" src=\"";
			echo $script;
			echo "\" language=\"javascript\"></script>";
	    }
		
		if(DEBUG_MODE) {
			Profiler::finish("Template[Footer]");
			Profiler::finish("Template");
		
			echo "<center><div style=\"clear: both; margin-top: 80px; padding: 5px; background-color: black; background-color: rgba(0, 0, 0, 0.7); color: white; font-size: 10px;\"><a style=\"font-size: 12px; color: white; font-weight: bold;\" href=\"";
			echo REQUEST_URL;
			echo "?dumpstate\">Debug Information</a><br />";
			echo CachedObject::countInstances() . " Cached Objects Loaded<br />";
			echo ClassLoader::countLoadedClasses() . " Classes Loaded<br />";
			echo Database::countQueries() . " SQL Queries<br /><br />";
			echo "<span style=\"font-size: 11px; font-weight: bold;\">Profiler</span>";
			$labels = "";
			$data = "";
			foreach(Profiler::getTimers() as $key => $value){
				if($value < 1 || $key == "Total")
					continue;
				if($labels)
					$labels .= "|";
				$labels .= urlencode($key);
				if($data)
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
			foreach(Profiler::getTimers() as $key => $value) {
				echo "<tr";
				if($alt)
					echo " style=\"background-color: rgba(0, 0, 0, 0.2)\"";
				echo "><td style=\"padding-right: 20px;\">$key</td>";
				echo "<td align=\"right\">$value</td></tr>";
				$alt = !$alt;
			}
			echo "<tr style=\"background-color: rgba(0, 0, 0, 0.5)\">";
			echo "<td>Total</td><td>";
			echo number_format((microtime(true) - START_TIME)*1000, 2);
			echo "</td></tr></table></center></div>";
		}
		echo "</body></html>";
	}
	
}

function requireAddon($script){
	Template::importPrototypeAddon($script);
}

Template::init();
?>
