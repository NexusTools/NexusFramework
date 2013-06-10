<?php
class PageModule {

	private static $arguments;
	private static $globals;
	private static $workingPath;
	private static $instance;
	private $theme;
	private $error = false;
	private $headscript = false;
	private $script = false;
	private $sidebarLayout;
	private $rightSidebarScript;
	private $leftSidebarScript;
	private $themePath;
	private $pageTitle = false;
	private $realUri = false;
	private $html = false;
	private $badCond = false;
	private $get;
	private $post;
	
	const RAW_SIDEBAR_OUTPUT = false;
	const NO_SIDEBARS = 0;
	const LEFT_SIDEBAR = 1;
	const RIGHT_SIDEBAR = 2;
	const BOTH_SIDEBARS = 3;
	
	public function getHTML(){
		if(!$this->html) {
			$this->html = "";
			$this->run(true);
		}
		return $this->html;
	}
	
	public static function getWorkingPath(){
		return self::$workingPath;
	}
	
	public static function pathExists($path){
		new PageModule($path);
	}
	
	public static function setValue($key, $val){
		self::$globals[$key] = $val;
	}
	
	public static function hasValue($key){
		return isset(self::$globals[$key]) && self::$globals[$key] !== null;
	}
	
	public static function getValue($key){
	    $data = self::$globals;
	    foreach(func_get_args() as $arg){
	        if(!array_key_exists($arg, $data))
	            return false;
	        $data = $data[$arg];
	    }
	    
		return $data;
	}
	
	public static function countArguments(){
		return count(self::$arguments);
	}
	
	public static function getArgument($index){
		return self::$arguments[$index];
	}
	
	public static function getArguments(){
		return self::$arguments;
	}
	
	private function getError(){
		return $this->error;
	}
	
	private function setError($status){
		switch($status){
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
				$this->error = Array("code" => 500,
								 "message" => "Internal Error",
								 "body" => "<p>An error occured while processing your request.<br />It is not possible to recover from this error.<br /><br />Our technicians have been notified and will look into it as soon as possible.<br />Sorry for any Inconvenience.</p><pre style='font-size: 85%'><b>Error Message</b><br />" . framework_get_error_message() . "</pre>");
				break;
				
			default:
				$this->error = Array("code" => $status,
								 "message" => "Unhandled Status Code",
								 "body" => "<p>So apparently you've decided to start entering random status codes,<br />well here's a list of <a href=\"http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html\">HTTP Status Codes</a>.<br /><br />If you didn't mean to get this page, please contact the administrator to report this.</p>");
				break;
		}
		
		$this->pageTitle = $this->error['message'];
	}
	
	private function exploreVirtualPaths($arguments, $pureVirtualPaths){
		if(!isset($pureVirtualPaths['p']) || $this->script)
			return true;
	
		foreach($pureVirtualPaths['p'] as $path){
			if($this->findModule($path, $arguments))
				return true;
		}
		
		if(!$this->script && isset($pureVirtualPaths['v']) && count($arguments) > 0) {
			$path = array_shift($arguments);
			if(isset($pureVirtualPaths['v'][$path]))
				return $this->exploreVirtualPaths($arguments, $pureVirtualPaths['v'][$path]);
		}
		
		return false;
			
	}
	
	public static function hasError(){
		return self::$instance->error !== false;
	}
	
	public function __construct($path, $pureVirtualPaths=false, $ignorePrepend=false){
		if(DEBUG_MODE) {
			Profiler::start("PageModule");
			Profiler::start("PageModule[Constructor]");
		}
	
		self::$instance = $this;
		$this->get = $_GET;
		$this->post = $_POST;
		if(defined("THEME"))
			$this->themePath = "themes" . DIRSEP . THEME . DIRSEP;
		else
			$this->themePath = FRAMEWORK_PATH . "media-theme" . DIRSEP;
		
		self::$globals = Array();
		
		if((!LEGACY_OS && startsWith($path, "about:")) ||
		        (LEGACY_OS && startsWith($path, "about-"))) {
			self::$workingPath = cleanpath("/$path");
			self::$arguments = Framework::splitPath(substr($path, 6));
			$this->findModule(FRAMEWORK_PATH . "resources" . DIRSEP . "about" . DIRSEP, self::$arguments);
		} else {
			if(!defined("PATH_PREPEND") || $ignorePrepend)
				self::$workingPath = cleanpath("/$path");
			else
				self::$workingPath = cleanpath("/" . PATH_PREPEND . "/$path");
			self::$arguments = Framework::splitPath(self::$workingPath);
			
			if(count(self::$arguments) == 2 && self::$arguments[0] == "errordoc"
					&& is_numeric(self::$arguments[1]))
				$this->setError(intval(self::$arguments[1]));
			else {
				if(!$this->findModule(INDEX_PATH . "pages/", self::$arguments)) {
					if($pureVirtualPaths === false)
						$pureVirtualPaths = ExtensionLoader::getVirtualPaths();
					$this->exploreVirtualPaths(self::$arguments, $pureVirtualPaths);
				}
			}
		}
		
		if(!$this->script && !$this->error)
			$this->setError($this->badCond ? BAD_CONDITION_STATUS : 404);
		
		if($this->error !== false) {
			self::$workingPath = "/errordoc/" . $this->error['code'];
			self::$arguments = Array("errordoc", $this->error['code']);
		    
			if(!$this->findModule(INDEX_PATH . "pages/", self::$arguments)){
				if($pureVirtualPaths === false)
					$pureVirtualPaths = ExtensionLoader::getVirtualPaths();
					
				if(!$this->exploreVirtualPaths(self::$arguments, $pureVirtualPaths)) {
				    //header("Content-Type: text/plain");
		            //OutputHandlerStack::stop();
		            $base = INDEX_PATH . "pages" . DIRSEP;
		            $sidebarPaths = Array("errordoc/root", "errordoc", "root");
		            
		            foreach($sidebarPaths as $path){
		                if($this->sidebarLayout === false && is_file("$base$path.sidebars")) 
		                    $this->sidebarLayout = trim(file_get_contents("$base$path.sidebars")) * 1;
		                    
		                if(!$this->leftSidebarScript && is_file("$base$path.sidebar.left.inc.php"))
		                    $this->leftSidebarScript = "$base$path.sidebar.left.inc.php";
		                if(!$this->rightSidebarScript && is_file("$base$path.sidebar.right.inc.php"))
		                    $this->rightSidebarScript = "$base$path.sidebar.right.inc.php";
		            }
		            //die();
				}
			}
		}

		if(DEBUG_MODE)
			Profiler::finish("PageModule[Constructor]");
	}
	
	public static function setThemePath($path){
	    self::$instance->themePath = fullpath($path);
	}
	
	public function initialize($changeStatus=true){
		if(DEBUG_MODE)
			Profiler::start("PageModule[Initialize]");
		
		$_GET = $this->get;
		$_POST = $this->post;
		if($this->pageTitle)
			Template::setTitle($this->pageTitle);
		
		self::$instance = $this;
		if($this->headscript)
			require $this->headscript;
			
		$this->theme = new Theme($this->themePath);
			
		if($changeStatus && $this->error)
			header("http/1.1 " . $this->error['code'] . " " . $this->error['message']);
		
		if(DEBUG_MODE) {
			Profiler::finish("PageModule[Initialize]");
			Profiler::finish("PageModule");
		}
	}
	
	private function findModule($basepath, $parts, $resetFindings=true) {
	    if($resetFindings) {
	        $this->sidebarLayout = false;
	        $this->rightSidebarScript = false;
	        $this->leftSidebarScript = false;
	    }
	    
		$_GET = $this->get;
		$_POST = $this->post;
		$mname = count($parts) > 0 ? array_shift($parts) : "root";
		if(is_file($basepath ."root.exists.inc.php")) {
			$ret = require($basepath ."root.exists.inc.php");
			if($ret === false)
				return false;
			else if(is_string($ret)) {
				if(startsWith($ret, "/"))
					return PageModule::findModule("$ret/", $parts, false);
				else
					return PageModule::findModule("$basepath" . relativepath($ret) . "/", $parts, false);
			}
		}
		if(is_file($basepath ."root.cond.inc.php") &&
				!require($basepath ."root.cond.inc.php")) {
			$this->badCond = true;
			return false;
		}
		
		if(is_file($basepath . "root.sidebars"))
		    $this->sidebarLayout = trim(file_get_contents($basepath . "root.sidebars")) * 1;
		if(is_file($basepath . "root.sidebar.left.inc.php"))
		    $this->leftSidebarScript = $basepath . "root.sidebar.left.inc.php";
		if(is_file($basepath . "root.sidebar.right.inc.php"))
		    $this->rightSidebarScript = $basepath . "root.sidebar.right.inc.php";
		
		$dirExists = null;
		if(count($parts)) {
		    if(is_dir("$basepath$mname"))
			    return $this->findModule("$basepath$mname/", $parts, false);
		} else {
		    if($mname != "root" && is_dir("$basepath$mname"))
		        return $this->findModule("$basepath$mname/", $parts, false);
		
		    if(is_file("$basepath$mname.redirect"))
		        Framework::redirect(file_get_contents(fullpath("$basepath$mname.redirect")));
		    else if(is_file("$basepath$mname.inc.php")) {
			    if(is_file("$basepath$mname.virt.inc.php")) {
				    $ret = require("$basepath$mname.virt.inc.php");
				
				    if(!$ret || $ret == null)
					    return false;
				    else if(is_numeric($ret)) {
					    if($ret >= 100 && $ret <= 500) {
						    $this->setError($ret);
						    return false;
					    } else
						    return false;
				    }
			    } else if(count($parts))
				    return false;
				
				if($mname != "root") {
				    if(is_file("$basepath$mname.sidebars"))
		                $this->sidebarLayout = trim(file_get_contents("$basepath$mname.sidebars")) * 1;
		            if(is_file("$basepath$mname.sidebar.left.inc.php"))
		                $this->leftSidebarScript = "$basepath$mname.sidebar.left.inc.php";
		            if(is_file("$basepath$mname.sidebar.right.inc.php"))
		                $this->rightSidebarScript = "$basepath$mname.sidebar.right.inc.php";
			        if(is_file("$basepath$mname.cond.inc.php") &&
					        !require("$basepath$mname.cond.inc.php")) {
				        $this->badCond = true;
				        return false;
			        }
			    }
				
			    if(is_file("$basepath$mname.title"))
				    $this->pageTitle = trim(file_get_contents("$basepath$mname.title"));
				
		        
			    if(is_file("$basepath$mname.head.inc.php"))
				    $this->headscript = fullpath("$basepath$mname.head.inc.php");
			
			    $this->script = fullpath("$basepath$mname.inc.php");
			    
			    
			    $this->get = $_GET;
			    $this->post = $_POST;
			    return true;
		    }
        }
        
        if(is_file($basepath . "virtual.exists.inc.php")) {
		    $ret = require($basepath . "virtual.exists.inc.php");
		
		    if(!$ret || $ret == null)
			    return false;
		    else if(is_numeric($ret) && $ret != 200) {
			    if($ret >= 100 && $ret <= 500) {
				    $this->setError($ret);
				    return false;
			    } else
				    return false;
		    } else if(is_string($ret)) {
				if(startsWith($ret, "/"))
					return PageModule::findModule("$ret/", $parts, false);
				else
					return PageModule::findModule("$basepath" . relativepath($ret) . "/", $parts, false);
			}
		
		    if(is_file($basepath . "virtual.cond.inc.php") &&
				    !require($basepath . "virtual.cond.inc.php")) {
			    $this->badCond = true;
			    return false;
		    }
			
		    if(is_file($basepath . "virtual.head.inc.php"))
			    $this->headscript = $basepath . "virtual.head.inc.php";
	
		    $this->script = $basepath . "virtual.inc.php";
		    if(!is_file($this->script))
			    throw new IOException($this->script, IOException::NotFound, "PageModule provides virtual existence script, but no page implementation.");
			    
			$this->get = $_GET;
			$this->post = $_POST;
			return true;
	    }
	    
	    return false;
	}
	
	public function addOutputBuffer($html){
		$this->html .= $html;
	}
	
	public function run($capture=false, $onlyPageArea=false){
		if(DEBUG_MODE)
			Profiler::start("PageModule[Script]");
			
		if($capture || $onlyPageArea){
			$this->html = "";
			OutputHandlerStack::pushOutputHandler(array($this, "addOutputBuffer"));
		}
		
		if(!$onlyPageArea) {
		    if($this->sidebarLayout !== self::RAW_SIDEBAR_OUTPUT) {
		        if($this->sidebarLayout == self::LEFT_SIDEBAR
		                || $this->sidebarLayout == self::BOTH_SIDEBARS) {
		            echo "<column class='sidebar left'><contents>";
		            require $this->leftSidebarScript;
		            echo "</contents></column>";
		        }
		        echo "<column class='pagearea";
		        switch($this->sidebarLayout) {
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
		        echo "'><contents>";
		    }
		}
		if($this->script)
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
		if(!$onlyPageArea && $this->sidebarLayout !== self::RAW_SIDEBAR_OUTPUT) {
		    echo "</contents></column>";
		    
		    if($this->sidebarLayout == self::RIGHT_SIDEBAR
		            || $this->sidebarLayout == self::BOTH_SIDEBARS) {
		        echo "<column class='sidebar right'><contents>";
		        require $this->rightSidebarScript;
		        echo "</contents></column>";
		    }
		}
		
		if($capture || $onlyPageArea)
			OutputHandlerStack::popOutputHandler();
		
	    if($onlyPageArea) {
	        if($this->sidebarLayout === self::RAW_SIDEBAR_OUTPUT
	                && preg_match("/<column\s.*?class=['\"].*?pagearea.*?['\"].*?>[\s\n]*?<contents.*?>((.|\n)+?)<\/contents>[\s\n]*?<\/column>/i", $this->html, $matches))
	            $this->html = trim($matches[1]);
	        if(!$capture)
	            echo $this->html;
	    }
	    
		if(DEBUG_MODE)
			Profiler::finish("PageModule[Script]");
	}
	
	public function getTheme(){
		return $this->theme;
	}
	
	public function __toString(){
		$this->run(true);
		return $this->html;
	}
	
}
?>
