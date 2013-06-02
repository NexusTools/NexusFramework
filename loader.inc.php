<?php if(defined("LOADER_START_TIME"))	return;
define("LOADER_START_TIME", microtime(true));

// Setup Output Buffering
define("NATIVE_OB_LEVEL", ob_get_level());
if(!ob_get_level())
    ob_start();
    
// Dump Early Errors
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Test PHP Version
if(floatval(phpversion()) < 5.3)
	throw new Exception("PHP 5.3 or Higher Required.");

// Shorter Aliases
define("DIRSEP", DIRECTORY_SEPARATOR);
define("PATHSEP", PATH_SEPARATOR);

// Helper Constants
define("PHP_INT_MIN", -PHP_INT_MAX);

// Setup Common Paths
define("FRAMEWORK_PATH", dirname(__FILE__) . DIRSEP);
define("FRAMEWORK_CORE_PATH", FRAMEWORK_PATH . "core" . DIRSEP);
define("FRAMEWORK_EXT_PATH", FRAMEWORK_PATH . "extensions" . DIRSEP);
define("FRAMEWORK_RES_PATH", FRAMEWORK_PATH . "resources" . DIRSEP);
define("FRAMEWORK_MODULE_PATH", FRAMEWORK_PATH . "modules" . DIRSEP);

// Find Website Root
$pos = 0;
$stack = debug_backtrace(0);
$fmpath_len = strlen(FRAMEWORK_PATH);
while($pos < count($stack)) {
	if(substr_compare($stack[$pos]['file'], FRAMEWORK_PATH, 0, $fmpath_len) !== 0) {
		define("INDEX_FILE", $stack[$pos]['file']);
		define("INDEX_PATH", dirname(INDEX_FILE) . DIRSEP);
		break;
	} else
		$pos++;
}
unset($fmpath_len);
unset($stack);
unset($pos);

define("EXT_PATH", INDEX_PATH . "extensions" . DIRSEP);

require FRAMEWORK_CORE_PATH . "functions.inc.php";
set_include_path(FRAMEWORK_PATH . PATHSEP . INDEX_PATH);

// Request Defines
$dmn = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : "localhost";
if(isset($_SERVER['HTTP_PORT']))
	define("DOMAIN", $dmn
		. ($_SERVER['HTTP_PORT'] != 80 ? ":" . $_SERVER['HTTP_PORT'] : ""));
else
	define("DOMAIN", $dmn);

if(preg_match("/([\w\d]+)\.(\w+)$/", $dmn, $matches)) {
	define("DOMAIN_TL", $matches[2]);
	define("DOMAIN_SL", $matches[1]);
} else {
	define("DOMAIN_TL", "");
	define("DOMAIN_SL", $dmn);
}
unset($dmn);

define("PROTOCOL_SECURE", isset($_SERVER['HTTPS']));
define("PROTOCOL", PROTOCOL_SECURE ? "https" : "http");

$pathoffset = strlen(INDEX_PATH) - strlen($_SERVER['DOCUMENT_ROOT']);
if($pathoffset <= 0)
	$pathoffset = 1;
define("BASE_URI", substr($_SERVER['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']), 0, $pathoffset));
$uri = substr($_SERVER['REQUEST_URI'], $pathoffset - 1);
if(($pos = strpos($uri, "?")) !== false)
	$uri = substr($uri, 0, $pos);
define("REQUEST_URI", $uri);
define("BASE_URL", PROTOCOL . "://" . DOMAIN . BASE_URI);
define("REQUEST_URL", BASE_URL . substr(REQUEST_URI, 1));
unset($pathoffset);
unset($uri);
unset($pos);

// Client Specific Defines
if(strncasecmp(PHP_OS, 'WIN', 3) == 0) {
	define("LEGACY_OS", true);
	define("RES_CONNECTOR", "-");
} else {
	define("LEGACY_OS", false);
	define("RES_CONNECTOR", ":");
}

define("LEGACY_BROWSER", isset($_SERVER['HTTP_USER_AGENT']) && 
        preg_match('/(?i)msie [1-9]/',$_SERVER['HTTP_USER_AGENT']));

// Setup Custom REQUEST
$_REQUEST = array_merge($_GET, $_POST);

// Load Framework Version
define("FRAMEWORK_VERSION", file_get_contents(FRAMEWORK_RES_PATH . "version"));

// Load Error Handling Hooks
require FRAMEWORK_CORE_PATH . "error-handling.inc.php";

// Process Framework Mode
if(!defined("FRAMEWORK_MODE"))
	define("FRAMEWORK_MODE", "website");

$loaderPath = FRAMEWORK_CORE_PATH . FRAMEWORK_MODE . "-loader.inc.php";
if(is_file($loaderPath))
	require $loaderPath;
else
	throw new Exception("Missing Loader for Mode `" . FRAMEWORK_MODE . "`");

?>
