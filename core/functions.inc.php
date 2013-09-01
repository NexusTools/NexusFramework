<?php
function require_chdir($file, $path=false){
	$owd = getcwd();
	if($path === false) {
		$path = dirname($file) . DIRSEP;
		$file = substr($file, strlen($path));
	}
	
	if(!is_dir($path))
	    die("Attempt to chdir to: $path");
	chdir($path);
	if(!is_file($file)) {
		chdir($owd);
		throw new IOException($path . $file, IOException::NotFound);
	}
	if(!is_readable($file)) {
		chdir($owd);
		throw new IOException($path . $file, IOException::ReadAccess);
	}

	$ret = include($file);
	chdir($owd);
	return $ret;
}

function endswith($string, $test){
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, -$testlen) === 0;
}

function startswith($string, $test)
{
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, 0, $testlen) === 0;
}

function to_php($object, $padding=""){
	if(is_array($object)){
		$out = "Array(";
		$padding .= str_repeat(" ", 8);
		$first = true;
		if(is_assoc($object)) {
			foreach($object as $key => $part) {
				if($first)
					$first = false;
				else
					$out .= ",";
				
				$out .= "\n$padding\"$key\" => ";
				$out .= to_php($part, str_repeat(" ", 4) . $padding);
			}
		} else {
			foreach($object as $part) {
				if($first)
					$first = false;
				else
					$out .= ",";
					
				$out .= "\n$padding";
				$out .= to_php($part, str_repeat(" ", 4) . $padding);
			}
		}
		$out .= "\n" . str_repeat(" ", strlen($padding) - 4) . ")";
	} else if(is_numeric($object))
		$out = "$object";
	else if(is_string($object))
		$out = "\"$object\"";
	else
		$out = "null";
	
	return $out;
}

function urlpath($url){
	if(preg_match("/^\w+:/", $url, $match))
		return $url;
		
	return cleanpath(BASE_URI . $url);
}

function relativepath($path){
	$path = cleanpath($path);
	while(startsWith($path, "/"))
		$path = substr($path, 1);
	return $path;
}

function properpath($path){
    $path = cleanpath($path);
    if(is_dir($path) && !endsWith($path, "/"))
        return $path . "/";
    return $path;
}

function cleanpath($path){
	$path = preg_replace("/\/+/", "/", $path);
	$path = str_replace("../", "", $path);
	$path = str_replace("./", "", $path);
	if($path == ".")
		return "";
	
	if($path == "..")
		return "";
	
	return $path;
}

function shortpath($path){
	$path = fullpath($path);

	if(startsWith($path, FRAMEWORK_PATH))
		return cleanpath(":/" . substr($path, strlen(FRAMEWORK_PATH)));
	else if(startsWith($path, INDEX_PATH))
		return cleanpath("^/" . substr($path, strlen(INDEX_PATH)));
	else if(startsWith($path, MEDIA_PATH))
		return cleanpath("#/" . substr($path, strlen(MEDIA_PATH)));
	else if(startsWith($path, BASE_TMP_PATH))
		return cleanpath("*/" . substr($path, strlen(BASE_TMP_PATH)));
		
	return $path;
}

function fullpath($path){
	global $__framework_activePath;

	if(startsWith($path, INDEX_PATH) || startsWith($path, BASE_TMP_PATH)
	    	|| startsWith($path, MEDIA_PATH) || startsWith($path, FRAMEWORK_PATH)
	    	|| startsWith($path, Theme::getPath()))
		return properpath($path);
	else if(startsWith($path, ":" . DIRSEP))
		return properpath(FRAMEWORK_PATH . substr($path, 1));
	else if(startsWith($path, "^" . DIRSEP))
		return properpath(INDEX_PATH . substr($path, 1));
    else if(startsWith($path, "#" . DIRSEP))
		return properpath(MEDIA_PATH . substr($path, 1));
	else if(startsWith($path, "*" . DIRSEP))
		return properpath(BASE_TMP_PATH . substr($path, 1));
	
	if(file_exists($rpath = getcwd() . DIRSEP . $path))
		return properpath($rpath);
	
	if(file_exists($rpath = MEDIA_PATH . DIRSEP . $path))
		return properpath($rpath);
	
    if(file_exists($rpath = Theme::getPath() . DIRSEP. $path))
	    return properpath($rpath);
		
    if(isset($__framework_activePath) &&
    		file_exists($rpath = $__framework_activePath . DIRSEP . $path))
		return properpath($rpath);
		
	if(file_exists($rpath = FRAMEWORK_PATH . DIRSEP . $path))
		return properpath($rpath);
	
	return properpath(getcwd() . DIRSEP . $path);
}

function proxyRequest($domain, $port=80, $hostname=false){
	if(!class_exists("RequestProxy"))
		require(FRAMEWORK_CLASSPATH . "RequestProxy.class.php");
	new RequestProxy($domain, $port, $hostname);
}

function redirectDomain($newDomain){
	header("Location: " . PROTOCOL . "://$newDomain" . REQUEST_URI);
	exit;
}

function redirect($newPath){
	header("Location: $newPath");
	exit;
}

function is_assoc($array) {
    return ($object instanceof Traversable) || 
    			(is_array($array) && (count($array)==0 ||
    				0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
}

function is_iterable($object) {
	return ($object instanceof Traversable) || is_array($object);
}

if(!function_exists("getallheaders")) {
	function getallheaders() {
		foreach($_SERVER as $h=>$v)
			if(preg_match('/HTTP_(.+)/i',$h,$hp)) {
				$key = "";
				$upper = true;
				foreach(str_split($hp[1]) as $char){
					if($char == "_"){
						$key .= '-';
						$upper = true;
						continue;
					}
					
					if($upper) {
						$key .= $char;
						$upper = false;
					}else
						$key .= strtolower($char);
				}
				$headers[$key]=$v;
			}
			
		return $headers;
	}
}

function prependPath($path, $rootDomain){
	define("ROOT_DOMAIN", $rootDomain);
	define("PATH_PREPEND", $path);
}

function interpolate($string, $allowEval=true, $globals=Array()) {
	return StringTemplate::interpolate($string, $allowEval, $globals);
}
?>
