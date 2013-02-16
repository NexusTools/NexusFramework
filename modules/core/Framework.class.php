<?php

class Framework {

    const RawHash = 0;
    const Base64EncodedHash = 1;
    const HexEncodedHash = 2;
    const FilenameSafeHash = 3;
    const URLSafeHash = 4;
    
    static $customTags = Array();
    
    private static $pageContent = "";
    
    public static function isHeadRequest(){
        return $_SERVER['REQUEST_METHOD'] == "HEAD";
    }
    
    public static function pushContent($content){
        self::$pageContent .= $content;
    }
	
	public static function runErrorPage($num, $status=false){
		if($status)
			header("HTTP/1.1 $num $status");
		self::runPage("errordoc/$num", !$status);
	}
	
	public static function getConfigFolder($folder){
	    $rfolder = cleanpath(CONFIG_PATH . $folder . DIRSEP);
	    if(!is_dir($rfolder) && !mkdir($rfolder, 0777, true))
	        throw new Exception("Failed to make config folder `$folder`");
	    return $rfolder;
	}
	
	public static function getTempFolder($folder, $shared=false){
	    $rfolder = cleanpath(($shared ? SHARED_TMP_PATH : TMP_PATH) . $folder . DIRSEP);
	    if(!is_dir($rfolder) && !mkdir($rfolder, 0777, true))
	        throw new Exception("Failed to make temp folder `$folder`");
	    return $rfolder;
	}
	
	public static function redirect($path, $seeOther=false, $rawUri=false){
		if(defined("INAPI"))
			return;
			
		if(headers_sent())
			throw new Exception("Cannot Redirect: Headers Already Sent");
		
	    if(!$rawUri && !preg_match("/^\w{2,6}:/", $path))
	        $path = BASE_URI . relativepath($path);
	    
		header("location: $path");
		if(is_string($seeOther)) {
			header("HTTP/1.1 303 $seeOther");
			self::runPage("errordoc/303", false);
		} else {
			header("HTTP/1.1 302 Found");
			self::runPage("errordoc/302", false);
		}
		
		exit();
	}
	
	public static function fileETag($file) {
		$fs = stat($file);
		return sprintf('%x-%x-%s', $fs['ino'], $fs['size'],base_convert(str_pad($fs['mtime'],16,"0"),10,16));
	}
	
	public static function testCondition($condition){
		if(!is_string($condition) || !strlen($condition))
			return true;
			
		return eval("return $condition;");
	}
	
	public static function serveFile($file, $mimetype=false, $realName=false){
	    ignore_user_abort(false);
		if(!is_file($file = fullpath($file)) || !($size = filesize($file)))
		    self::runPage("/errordoc/404");
		
		$etag = self::fileETag($file);
		$modtime = self::formatGMTDate(filemtime($file));
		if(!is_string($mimetype) || !strlen($mimetype = trim($mimetype))) {
			$mimetype = self::mimeForFile($file);
			if($mimetype && !startsWith($mimetype, "text/") && !startsWith($mimetype, "application/")
					&& !endsWith($mimetype, "+xml"))
				header("X-Content-Type-Options: nosniff");
		} else
			header("X-Content-Type-Options: nosniff");
		if(headers_sent($header_file, $header_line)) {
		    while(ob_get_level())
		        ob_end_clean();
		    die("Headers Already Sent by: $header_file:$header_line");
		}
		
		header("Content-Type: $mimetype");
		header("Last-Modified: $modtime");
		header('Accept-Ranges: bytes');
		header("ETag: $etag");
		
		if(is_string($realName) || !startsWith($file, BASE_TMP_PATH)) {
			$safeFilename = urlencode(is_string($realName) ? $realName : basename($file));
			if(!startsWith($mimetype, "text/") && !startsWith($mimetype, "image/")
					 && !startsWith($mimetype, "video/")  && !startsWith($mimetype, "audio/")
			          && !startsWith($mimetype, "application/"))
				header("Content-Disposition: attachment; filename=\"$safeFilename\"");
			else
				header("Content-Disposition: inline; filename=\"$safeFilename\"");
			
			header("X-Filename: $safeFilename");
		}
		
		header_remove("Cache-Control");
		header_remove("Expires");
		header_remove("Pragma");
		
		if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $range)) {
		    $range = Array(intval($range[1]), intval($range[2]));
		    if($range[1] < 1)
		        $range[1] = $size - 1;
		    
		    $length = $range[1] - $range[0];
		    
		    if($length > 0 && $length < $size) {
		        header('HTTP/1.1 206 Partial Content');
		        header("Content-Range: bytes $range[0]-$range[1]/$size");
		        $length++;
		        header("Content-Length: $length");
		        
		        while(ob_get_level())
		            ob_end_clean();
		        
		        $reader = fopen($file, "r");
		        if($range[0])
		            fseek($reader, $range[0]);
		        
		        while($length > 0){
		            $buffer = fread($reader, $length > 5120 ? 5120 : $length);
		            $length -= 5120;
		            print($buffer);
		            flush();
		        }
		        
		        die();
		    }
		}
		
		$headers = getallheaders();
		if((isset($headers['If-None-Match']) && $headers['If-None-Match'] == $etag) ||
				(isset($headers['If-Modified-Since']) && $headers['If-Modified-Since'] == $modtime)) {
			header("HTTP/1.1 304 Not Modified");
			die();
		}
		
		header("Content-Length: $size");
		
		while(ob_get_level())
		    ob_end_clean();
		    
		if(!self::isHeadRequest())
		    readfile($file);
		
		die();
	}
	
	public static function registerCustomTag($name){
	    if(!in_array($name, self::$customTags))
	        array_push(self::$customTags, $name);
	}
	
	public static function customTags(){
	    return self::legacyTags();
	}
	
	public static function legacyTags(){
	    return self::$customTags;
	}
	
	public static function legacyTagSwitch(){
	    $string = "";
	    foreach(self::legacyTags() as $tag){
	        if(strlen($string))
	            $string .= "|";
	        $string .= preg_quote($tag);
	    }
	    
	    return $string;
	}
	
	public static function formatGMTDate($timestamp=false){
		if(!$timestamp)
			$timestamp = time();
		return gmdate("D, d M Y H:i:s \G\M\T", $timestamp);
	}
	
	private static function processTagClass($matches){
	    $matches[2] = trim($matches[2]);
	    $matches[1] = strtolower(preg_replace("/[^\w]/", "_", $matches[1]));
	    if(!strlen($matches[2]))
	        return "<div class=\"$matches[1]\">";
	    
	    if(preg_match("/class=(\"[\w\d\s\-\_]+\"|'[\w\d\s\-\_]+')/i", $matches[2], $newMatches)) {
	        $matches[2] = trim(preg_replace("/class=(\"[\w\d\s\-\_]+\"|'[\w\d\s\-\_]+')/i", "", $matches[2]));
	        $matches[1] = trim($matches[1] . " " . substr($newMatches[1], 1, strlen($newMatches[1]) - 2));
	    }
	    
	    return "<div class=\"$matches[1]\" $matches[2]>";
	}
	
	public static function finalize($cleanBuffer=true){
		try {
		    if($cleanBuffer)
			    while(ob_get_level())
		            ob_end_clean();
			else
			    while(ob_get_level() > NATIVE_OB_LEVEL)
		            ob_end_flush();
		}catch(Exception $e){}
		if(!$cleanBuffer) {
		    if(LEGACY_BROWSER && !defined("INAPI")) {
		        $tagSwitch = self::legacyTagSwitch();
	            self::$pageContent = preg_replace_callback("/<($tagSwitch)(|\s[^>]*?)>/i", "Framework::processTagClass", self::$pageContent);
	            self::$pageContent = preg_replace("/<\\/($tagSwitch)>/i", "</div>", self::$pageContent);
		        echo "<!-- This page has been automatically modified to support Internet Explorer -->";
			}
			echo self::$pageContent;
	    }
		define("ABORT_ERROR", true);
		exit;
	}
	
	public static function serveMediaFile($path){
		if(!startsWith($path, MEDIA_PATH))
			self::runPage("/errordoc/403");
		self::serveFile($path);
	}
	
	public static function serveResource($res, $onlyShared=false){
		
	    
		if(startsWith($res, "ref/")) {
			$res = substr($res, 4);
			if(!$onlyShared && is_file($refFile = TMP_PATH . "ref" . DIRSEP . $res) &&
							$data = unserialize(file_get_contents($refFile)))
				self::serveFile($data['path'], $data['mime'], isset($data['filename']) ? $data['filename'] : false);
			else if(is_file($refFile = SHARED_TMP_PATH . "ref" . DIRSEP . $res) &&
							$data = unserialize(file_get_contents($refFile)))
				self::serveFile($data['path'], $data['mime'], isset($data['filename']) ? $data['filename'] : false);
			else
				self::runPage("/errordoc/404");
		} else if(startsWith($res, "badref/") && is_file($refFile = TMP_PATH . "badref" . DIRSEP . substr($res, 7)) && $data = unserialize(file_get_contents($refFile))) {
			while(ob_get_level())
			    ob_end_clean();
			    
			echo "<html><head><title>Invalid Resource</title></head><body><h1>Invalid Resource</h1><p>The resource `$data[path]` is missing.";
			if(class_exists("User", false) && User::isStaff()) {
				echo "<h2>Backtrace</h2><pre>";
				print_r($data['backtrace']);
				echo "</pre>";
			}
			die("</p></body></html>");
		}
		
		switch($res){
		    case "internal-error":
		        while(ob_get_level())
		            ob_end_clean();
		        die("<html><head><title>Internal Error Occured</title></head><body><h1>Internal Error Occured</h1><p>An internal error occured while processing your request,<br />This error has been logged and we are working to fix it.<br />Sorry for any inconvenience.</p></body></html>");

            case "lgpl":
                self::serveFile(FRAMEWORK_RES_PATH . "LGPL3.0.html");
                
            case "license":
                self::serveFile(FRAMEWORK_RES_PATH . "license");

			case "script":
				while(ob_get_level())
		            ob_end_clean();
				$scmpr = new ScriptCompressor(true);
				$path = FRAMEWORK_RES_PATH . "javascript" . DIRSEP;
				
				$scmpr->addScript($path . "prototype.js");
			    foreach(glob($path . "framework" . DIRSEP . "*.js") as $script){
			    	$scmpr->addScript($script);
			    }
				$scmpr->dumpAsResponse();
				self::finalize();
		    
			case "style":
				while(ob_get_level())
		            ob_end_clean();
				$style = new CompressedStyle(FRAMEWORK_RES_PATH . "stylesheets" . DIRSEP . "widgets.css");
				$style->dumpAsResponse();
				self::finalize();
		
			case "information":
				header("Content-Type: text/plain");
				while(ob_get_level())
		            ob_end_clean();
				echo "Version: " . FRAMEWORK_VERSION;
				echo "\nInstall Path: " . FRAMEWORK_PATH;
				echo "\nBase URL: " . BASE_URL;
				self::finalize();
		}
		
		self::runPage("/errordoc/404");
	}
	
	public static function run($requestURI, $basePath){
		Profiler::start("Framework");
	    ignore_user_abort(true);
		ExtensionLoader::loadEnabledExtensions();
		
		if(!count($_POST) && !count($_GET)) {
			$clean = "/" . relativepath($requestURI);
			if(($cpath = $clean) != $requestURI)
				self::redirect($clean);
			unset($clean);
		}
		
		if($requestURI == "robots.txt" && is_file($file = INDEX_PATH . "robots.txt"))
			self::serveFile($file, "text/plain");

		if(startsWith($requestURI, "/media/")) {
			$file = cleanpath($basePath . $requestURI);
			if(file_exists($file))
				self::serveMediaFile($file);
			else
				self::runPage("/errordoc/404");
		}

		chdir($basePath);
		if(startsWith($requestURI, "/res" . RES_CONNECTOR))
			self::serveResource(substr($requestURI, 5));
		else if($requestURI == "/" && isset($_GET['api'])) {
			ExtensionLoader::registerAPICalls();
			if(DEBUG_MODE)
				Profiler::finish("Framework");
	        
	        if(self::isHeadRequest())
		        die();
			API::run();
		}
		
		$rawpath = substr($requestURI, 1);
		if(file_exists("$rawpath.php") && REQUEST_URI != "/index" && REQUEST_URI != "/framework.config"){
			@chdir(dirname($rawpath));
			while(ob_get_level())
			    ob_end_clean();
			require("$rawpath.php");
			self::finalize();
		} else if(is_dir($rawpath) && file_exists("$rawpath/index.php")){
			@chdir($rawpath);
			while(ob_get_level())
			    ob_end_clean();
			require("index.php");
			self::finalize();
		}
		
		if(DEBUG_MODE)
			Profiler::finish("Framework");
		self::runPage($rawpath);
	}
	
	public static function isLegacyOS(){
	    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}
	
	public static function uniqueHash($string=false, $encodeOptions=self::FilenameSafeHash, $md5=false){
	    if(!$string) {
	    	if(function_exists("openssl_random_pseudo_bytes"))
	    		$string = openssl_random_pseudo_bytes(20);
	    	else if(function_exists("mt_rand"))
	        	$string = mt_rand();
	        else
	        	$string = rand();
		} else if(is_array($string))
			$string = json_encode($string);
		else if(is_object($string))
			$string = $string->toString();
	    
		if($md5)
			$rawHash = md5($string, true);
		else
			$rawHash = crc32($string);
        switch($encodeOptions){
            case self::Base64EncodedHash:
                return base64_encode($rawHash);
        
            case self::HexEncodedHash:
                return dechex($rawHash);
        
            case self::FilenameSafeHash:
                return str_replace("/", "_", base64_encode($rawHash));
                break;
                
            case self::URLSafeHash:
                return rawurlencode(base64_encode($rawHash));
                break;
                
            default:
                return $rawHash;
        }
	}
	
	public static function fileHash($file){
		return self::uniqueHash(stat($file));
	}
	
	public static function mimeForFile($path){
	    $fileMime = new FileMime($path);
	    return $fileMime->getData();
	}
	
	private static function getFileReference($rawPath, $mime_type, $realFilename, $shared, $relative){
		if(!is_file($path = fullpath($rawPath))) {
			if(!is_dir(($refPath = ($shared ? SHARED_TMP_PATH : TMP_PATH) . "badref" . DIRSEP)))
				mkdir($refPath);
			$id = self::uniqueHash($rawPath);
			if(!is_file(($ref_file = "$refPath$id")))
				file_put_contents($ref_file, serialize(Array("path" => $rawPath, "backtrace" => debug_backtrace(0))));
			return ($relative ? BASE_URI : BASE_URL) . "res" . RES_CONNECTOR . "badref/" . urlencode($id);
		}

		if(startsWith($path, MEDIA_PATH))
			return ($relative ? MEDIA_URI : MEDIA_URL) . substr($path, strlen(MEDIA_PATH));

		if($shared && startsWith($rawPath, TMP_PATH))
			$shared = false;

		if(!is_dir(($refPath = ($shared ? SHARED_TMP_PATH : TMP_PATH) . "ref" . DIRSEP)))
			mkdir($refPath);

		$id = self::uniqueHash($path);
		if(!is_file(($ref_file = "$refPath$id"))) {
		    if(!is_string($mime_type))
		        $mime_type = self::mimeForFile($path);
		    if(is_string($realFilename))
				file_put_contents($ref_file, serialize(Array("path" => $path, "mime" => $mime_type, "filename" => $realFilename)));
			else
				file_put_contents($ref_file, serialize(Array("path" => $path, "mime" => $mime_type)));
		}
		
		return ($shared ? ($relative ? SHARED_RESOURCE_URI : SHARED_RESOURCE_URL)
					: (($relative ? BASE_URI : BASE_URL) . "res" . RES_CONNECTOR)) . "ref/" . urlencode($id);
	}
	
	/*
	
		Framework::getReferenceURI()
		
		@returns [string] Reference URI for File or Resource
		
		Return the Reference for a File or Resource,
		Securing the Backend by Blocking Direct Access.
	
	*/
	public static function getReferenceURI($rawPath, $mime_type=false, $realFilename=false, $shared=true){
		return self::getFileReference($rawPath, $mime_type, $realFilename, $shared, true);
		
	}
	
	public static function getReferenceURL($rawPath, $mime_type=false, $realFilename=false, $shared=true){
		return self::getFileReference($rawPath, $mime_type, $realFilename, $shared, false);
	}
	
	public static function output($code){
		return $code;
	}
	
	public static function startOutputBuffer(){
	    OutputHandlerStack::pushOutputHandler("Framework::pushContent");
	}
	
	public static function runPage($path, $changeStatus=true){
	    ignore_user_abort(false);
		if(NOACCESS_MODE && ($baseDomain = preg_replace("/.+\.(\w+\.\w+)/", "$1", DOMAIN)))
			redirectDomain($baseDomain);
		
		header("Content-Type: text/html; charset=utf-8");
		header("X-Content-Type-Options: nosniff");
		
		$module = new PageModule($path);
		$module->initialize($changeStatus);
		$module->getTheme()->initialize();
		self::startOutputBuffer();
		
		if(DEBUG_MODE) {
			if(isset($_GET['dumpstate'])){
				Framework::dumpState();
				exit();
			}
			
			Profiler::start("Template");
		}
		
		if(self::isHeadRequest())
		    die();
		
		Template::writeHeader();
		echo "<framework:theme>";
		$module->getTheme()->runHeader();
		echo "<framework:page>";
		Triggers::broadcast("template", "page-header");
		$module->run();
		Triggers::broadcast("template", "page-footer");
		echo "</framework:page>";
		$module->getTheme()->runFooter();
		echo "</framework:theme>";
		Template::writeFooter();
		self::finalize(false);
	}
	
	public static function splitPath($path){
		return preg_split('/\//', $path,-1,PREG_SPLIT_NO_EMPTY);
	}
	
	public static function dumpState(){
		Profiler::start("StateDump");
		while(ob_get_level())
			ob_end_clean();
		echo "<html><head><title>Framework State Debugger</title></head><body>";
		
		DebugDump::dump("-ClassLoader");
		User::initAllBackends();
		foreach(ClassLoader::getLoadedClasses() as $name)
			if($name != "Profiler")
				DebugDump::dump("-$name");
		
		DebugDump::dump(get_loaded_extensions(), "PHP Extensions");
		DebugDump::dump(get_loaded_extensions(true), "Zend Extensions");
		DebugDump::dump(get_defined_constants(true), "Constants");
		DebugDump::dump($_SESSION, "Session");
		Profiler::finish("StateDump");
		DebugDump::dump(Profiler::getTimers(), "Profiler");
		
		echo "<br /><br /><span style=\"font-size: 10px;\">";
		echo "Took " . number_format((microtime(true) - LOADER_START_TIME)*1000, 2) . "ms to Generate.";
		echo "</span></body></html>";
	}
	
}

Framework::registerCustomTag("framework:theme");
Framework::registerCustomTag("framework:page");
Framework::registerCustomTag("framework:config");
Framework::registerCustomTag("header");
Framework::registerCustomTag("footer");
Framework::registerCustomTag("column");
Framework::registerCustomTag("contents");
Framework::registerCustomTag("widget");
?>