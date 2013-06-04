<?php
class API {
	
	private static $callbacks = Array();
	private static $apiData;
	private static $curArg;
	private static $encoders = Array();
	
	public static function registerEncoder($name, $func){
		self::$encoders[$name] = $func;
	}
	
	private static function array_to_xml($dataObject, &$xmlObject) {
		foreach($dataObject as $key => $value) {
		    if(is_array($value)) {
		        if(is_numeric($key))
		            $key = "node";
		        $subnode = $xmlObject->addChild("$key");
		        self::array_to_xml($value, $subnode);
		    }
		    else {
		        $xmlObject->addChild("$key","$value");
		    }
		}
	}
	
	public static function registerCallback($callback, $script, $const=false, $path=false){
		if(isset(self::$callbacks[$callback]))
			throw new Exception("Callback Already Registered");
		
		self::$callbacks[$callback] = Array("script" => fullpath($script),
											"const" => $const,
											"path" => $path ? $path : getcwd());
	}
	
	public static function registerCallbacks($calls, $relative=true){
		foreach($calls as $callback => $script)
			self::registerCallback($callback, $script, false, $relative ? dirname($script) : getcwd());
	}
	
	public static function getCurrentArugment(){
		return self::$curArg;
	}
	
	public static function getCurrentArgument(){
		return self::$curArg;
	}
	
	private static function parseURLArgs($data, &$storage){
		foreach(explode("&", $data) as $key){
			$value = "";
			if(($pos = strrpos($key, "=")) !== false){
				$value = substr($key, $pos+1);
				$key = substr($key, 0, $pos);
			}
			$storage[urldecode($key)] = urldecode($value);
		}
	}
	
	public static function run(){
		if(DEBUG_MODE) {
			Profiler::start("API");
			Profiler::start("API[PreProcessor]");
		}
		define("INAPI", true);
		header("X-Content-Type-Options: nosniff");
		ExtensionLoader::loadEnabledExtensions();
		ExtensionLoader::registerAPICalls();
		
		$dataObject = Array();
		self::$apiData = Array();
		$format = false;
		foreach($_GET as $key => $value){
			if($key == "api")
				continue;
			
			if($key == "format") {
				$format = $value;
				continue;
			}
			if(!$format)
				$format = "json";
			if(!isset(self::$callbacks[$key])){
				$dataObject[$key] = Array("ERROR" => "No Such API Found");
				continue;
			}
		
			$postData = Array();
			$getData = Array();
			if(($pos = strrpos($value, "?")) !== false){
				self::parseURLArgs(substr($value, $pos+1), $getData);
				$value = substr($value, 0, $pos);
			}
			
			if(isset($_POST[$key]))
				self::parseURLArgs($_POST[$key], $postData);
			
			self::$apiData[$key] = Array("arg" => urldecode($value),
										"post" => $postData,
										"get" => $getData);
		}
		if(!$format)
			$format = "help";
		
		if(DEBUG_MODE)
			Profiler::finish("API[PreProcessor]");
		
		foreach(self::$apiData as $key => $val){
			if(DEBUG_MODE)
				Profiler::start("API[$key]");
			self::$curArg = $val['arg'];
			
			try {
				$callback = self::$callbacks[$key];
				chdir($callback['path']);
			
				if(!file_exists($callback['script']))
					new IOException($callback['script'], IOException::NotFound);

				$_GET = $val['get'];
				$_POST = $val['post'];
				$_REQUEST = array_merge($_GET, $_POST);
				$dataObject[$key] = include($callback['script']);
			} catch(Exception $e) {
				$dataObject[$key] = Array("error" => $e->getMessage());
			}
			if(DEBUG_MODE)
				Profiler::finish("API[$key]");
		}
		
		Framework::startOutputBuffer();
		$encoder = isset(self::$encoders[$format]) ? self::$encoders[$format] : self::$encoders["help"];
		call_user_func($encoder, $dataObject);
		Framework::finalize(false);
	}
	
	public static function _debugEncoder($dataObject){
		header("Content-Type: text/html");
		echo "<html><head><title>Framework API Debugger</title></head><body>";
		echo "<h2>Framework API Debugger</h2>";
		DebugDump::dump(self::$encoders, "Encoders");
		DebugDump::dump(self::$callbacks, "Callbacks");
		DebugDump::dump(self::$apiData, "Callback State");
		DebugDump::dump($dataObject, "DataObject");
		if(DEBUG_MODE)
			Profiler::finish("API");
		DebugDump::dump(Profiler::getTimers(), "Profiler");
		echo "<br /><br /><span style=\"font-size: 10px;\">";
		echo "Took " . number_format((microtime(true) - LOADER_START_TIME)*1000, 2) . "ms to Generate.";
		echo "</span></body></html>";
	}
	
	private static function _ensureUTF8(&$dataObject){
		if(is_array($dataObject)) {
			foreach($dataObject as $key => &$value)
				self::_ensureUTF8($value);
		} else if(is_string($dataObject) && !mb_check_encoding($dataObject, 'UTF-8'))
			$dataObject = mb_convert_encoding($dataObject, 'UTF-8');
	}
	
	public static function _jsonEncoder($dataObject){
		header("Content-Type: application/json");
		
		self::_ensureUTF8($dataObject);
		
		$data = json_encode($dataObject);
		header("Content-Length: " . strlen($data));
		echo $data;
	}
	
	public static function _xmlEncoder($dataObject){
		header("Content-Type: application/xml");
		$xmlObject = new SimpleXMLElement('<api/>');
		self::array_to_xml($dataObject, $xmlObject);
		echo $xmlObject->asXML();
	}
	
	public static function _textEncoder($dataObject){
		header("Content-Type: text/plain");
		print_r($dataObject);
	}
	
	public static function _serializeEncoder($dataObject){
		header("Content-Type: text/php-serialized");
		echo serialize($dataObject);
	}
	
	public static function _helpEncoder($dataObject){
		header("Content-Type: text/html");
		echo "<html><head><title>Framework API Help</title></head><body>";
		echo "<h2>Framework API Help</h2>";
		
		echo "<b><font size=3>Available Formats</font></b><br />";
		foreach(self::$encoders as $encoder => $callback)
			echo "$encoder<br />";
			
		echo "<br /><b><font size=3>Available Callbacks</font></b><br />";
		foreach(self::$callbacks as $callback => $callback)
			echo "$callback<br />";
			
		echo "<br /><b><font size=3>Access Example</font></b><br />";
		$url = BASE_URL . "?api&format=xml&page=/";
		echo "<a href=\"$url\">$url</a><br />";
			
		echo "<br /><b><font size=3>Argument Layout</font></b><br />";
		echo "API arguments are normal url strings, they can contain GET and POST variables, and set the GET and POST global variables respectively server-side while running the API provider.<br />GET variables must be at the end of the GET argument for the API, the same way you would with a URL: ?key=val&key2=val2...<br />Where as POST variables must appear in the request POST header, assocated to the API's name.";
		echo "</body></html>";
	}
	
}

API::registerEncoder("xml", "API::_xmlEncoder");
API::registerEncoder("json", "API::_jsonEncoder");
API::registerEncoder("text", "API::_textEncoder");
API::registerEncoder("help", "API::_helpEncoder");
API::registerEncoder("serialize", "API::_serializeEncoder");

if(DEBUG_MODE)
	API::registerEncoder("debug", "API::_debugEncoder");

API::registerCallback("page", __DIR__ . DIRSEP . "page.inc.php");
?>
