<?php

if(function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) {
    $strip_slashes_deep = function ($value) use (&$strip_slashes_deep) {
        return is_array($value) ? array_map($strip_slashes_deep, $value) : stripslashes($value);
    };
    $_GET = array_map($strip_slashes_deep, $_GET);
    $_COOKIE = array_map($strip_slashes_deep, $_COOKIE);
}
if($_SERVER['REQUEST_METHOD'] == "POST" && !count($_FILES)) {
    $_POST = Array();
    foreach (explode("&", file_get_contents("php://input")) as $pair) {
        $nv = explode("=", $pair);
        $name = urldecode($nv[0]);
        $value = urldecode($nv[1]);
        if(isset($_POST[$name])) {
            if(!is_array($_POST[$name]))
                $_POST[$name] = Array($_POST[$name], $value);
            else
                array_push($_POST[$name], $value);
        } else
            $_POST[$name] = $value;
    }
}

if(strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== false)
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.') !== FALSE
	    || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.') !== FALSE) {
	    while(ob_get_level() > NATIVE_OB_LEVEL)
	        ob_end_clean();
	    require FRAMEWORK_CORE_PATH . "ie-upgrade.inc.php";
	    die();
    } else
        header("X-UA-Compatible: IE=Edge,chrome=1");

if(is_file(INDEX_PATH . "framework.config.php"))
	require INDEX_PATH . "framework.config.php";
else {
	while(ob_get_level() > NATIVE_OB_LEVEL)
		ob_end_clean();
	require FRAMEWORK_CORE_PATH . "installer.inc.php";
	return;
}

if(!defined("BASE_TMP_PATH")) {
	$tmpPath = cleanpath(sys_get_temp_dir() . DIRSEP . "NexusFramework");
	if(!is_writable(sys_get_temp_dir()) || (!is_dir($tmpPath) && !mkdir($tmpPath, 0777, true))) {
		$tmpPath = INDEX_PATH . "tmp" . DIRSEP . DOMAIN;
		if(!is_dir($tmpPath) && !mkdir($tmpPath, 0777, true))
			throw new Exception("Unable to Make Temporary Directory");
	}

	define("BASE_TMP_PATH", $tmpPath . DIRSEP);
	unset($tmpPath);
}
define("TMP_PATH", BASE_TMP_PATH . crc32(DOMAIN . INDEX_PATH) . DIRSEP);
define("SHARED_TMP_PATH", BASE_TMP_PATH . "Shared" . DIRSEP);

if(!defined("MEDIA_PATH"))
	define("MEDIA_PATH", INDEX_PATH . "media" . DIRSEP);
if(!defined("MEDIA_URL")) {
	define("MEDIA_URI", BASE_URI . "media/");
	define("MEDIA_URL", BASE_URL . "media/");
} else if(!defined("MEDIA_URI"))
	define("MEDIA_URI", MEDIA_URL);
define("NOACCESS_MODE", false);
if(!defined("SHARED_RESOURCE_URL")) {
    define("SHARED_RESOURCE_URL", BASE_URL . "res" . RES_CONNECTOR);
    define("SHARED_RESOURCE_URI", BASE_URI . "res" . RES_CONNECTOR);
} else if(!defined("SHARED_RESOURCE_URI"))
	define("SHARED_RESOURCE_URI", SHARED_RESOURCE_URL);
if(!defined("CONFIG_PATH"))
	define("CONFIG_PATH", INDEX_PATH . "config" . DIRSEP);

if(!defined("DEBUG_MODE"))
	define("DEBUG_MODE", false);
if(!defined("BAD_CONDITION_STATUS"))
	define("BAD_CONDITION_STATUS", 403);
	
// Load required classes
require FRAMEWORK_MODULE_PATH . "core" . DIRSEP . "Framework.class.php";
require FRAMEWORK_MODULE_PATH . "core" . DIRSEP . "cache" . DIRSEP . "CachedObject.class.php";
require FRAMEWORK_MODULE_PATH . "core" . DIRSEP . "cache" . DIRSEP . "CachedFileBase.class.php";
require FRAMEWORK_MODULE_PATH . "core" . DIRSEP . "cache" . DIRSEP . "FrameworkClassLocation.class.php";
require FRAMEWORK_MODULE_PATH . "core" . DIRSEP . "ClassLoader.class.php";

session_name("S" . dechex(ClientInfo::getUniqueID()));
if(!session_start())
    throw new Exception("Failed to start session...");

if(array_key_exists("ClientID", $_SESSION) &&
		$_SESSION["ClientID"] != ClientInfo::getUniqueID()) {
    session_regenerate_id();
    $_SESSION = Array("ClientID" => ClientInfo::getUniqueID(),
    				"NextUpdate" => time() + (3600 * 12));
} else if(!array_key_exists("ClientID", $_SESSION))
    $_SESSION = Array("ClientID" => ClientInfo::getUniqueID(),
    				"NextUpdate" => time() + (3600 * 12));
else if($_SESSION['NextUpdate'] < time()) {
    session_regenerate_id(true);
    $_SESSION["NextUpdate"] = time() + (3600 * 12);
}

Profiler::finish("Loader");
Framework::run(REQUEST_URI, INDEX_PATH);
?>
