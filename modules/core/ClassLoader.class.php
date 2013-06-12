<?php
class ClassLoader {

	private static $classes = Array();
	private static $classesByHash = Array();
	private static $loaded = Array();
	
	public static function getLoadedClasses(){
		return self::$loaded;
	}
	
	public static function getRegisteredClasses(){
		return self::$classes;
	}
	
	public static function countLoadedClasses(){
		return count(self::$loaded);
	}
	
	public static function registerClasses($classes){
		foreach($classes as $name => $file)
			self::registerClass($name, $file);
	}
	
	public static function classFileForHash($hash){
	    return self::$classesByHash[$hash];
	}
	
	public static function isHashRegistered($hash){
	    return array_key_exists($hash, self::$classesByHash);
	}
	
	public static function registerClass($name, $file){
		$file = fullpath($file);
		if(array_key_exists($name, self::$classes))
			return;
			//throw new Exception("Class Already Registered `$name`\nAttempted to register: $file\nAlready registered: " . self::$classes[$name]);
			
        self::$classesByHash[Framework::uniqueHash($file, Framework::Base64EncodedHash)] = $file;
		self::$classes[$name] = $file;
	}
	
	public static function resolve($name){
		if(!isset(self::$classes[$name])) {
			try {
				$locator = new FrameworkClassLocation($name);
				$location = $locator->getData();
				if(is_string($location))
					require($location);
			} catch(Exception $e) {}
		} else {
			$path = dirname(self::$classes[$name]);
			if(basename($path) == "classes")
				$path = dirname($path);
			
			if($path == ".")
				require(self::$classes[$name]);
			else {
				$filename = substr(self::$classes[$name], strlen($path) + 1);
				require_chdir($filename, $path);
			}
		}
		
		array_push(self::$loaded, $name);
	}

}

spl_autoload_register("ClassLoader::resolve");
?>
