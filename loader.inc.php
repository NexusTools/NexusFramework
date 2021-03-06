<?php
// Dump Early Errors
error_reporting(E_ALL);
ini_set("display_errors", 0);
ini_set("error_log", "php.err");

if (version_compare(PHP_VERSION, '5.3.0', '<'))
	die('PHP Version 5.3.0 or higher is required to run NexusFramework.');

// Test PHP Version
if (!defined("PHP_MAJOR_VERSION"))
	throw new Exception("PHP version incompatible.");
define("LOADER_START_TIME", microtime(true));

if (ini_get('zlib.output_compression'))
	define("COMPRESSED_OUTPUT", true);
if (function_exists("ob_gzhandler") && in_array("ob_gzhandler", ob_list_handlers()))
	define("COMPRESSED_OUTPUT", "gzip");
if (function_exists("ob_deflatehandler") && in_array("ob_deflatehandler", ob_list_handlers()))
	define("COMPRESSED_OUTPUT", "deflate");
if (!defined("COMPRESSED_OUTPUT"))
	while (ob_get_level() && @ob_end_clean());
else
	ob_start(null, 1048576, false); // Prevent destruction of built-in ob stack

// Setup Output Buffering
if (!function_exists("ob_void")) {
	function ob_void($data, $phase) {
		if (!strlen($data) && ($phase & PHP_OUTPUT_HANDLER_END) == PHP_OUTPUT_HANDLER_END)
			return false;
		return "";
	}
}
define("NATIVE_OB_LEVEL", ob_get_level());
ob_start("ob_void", 1048576);

// Shorter Aliases
define("DIRSEP", DIRECTORY_SEPARATOR);
define("PATHSEP", PATH_SEPARATOR);

// Helper Constants
define("PHP_INT_MIN", -PHP_INT_MAX);

// Setup Common Paths
define("FRAMEWORK_PATH", dirname(__FILE__).DIRSEP);
define("FRAMEWORK_CORE_PATH", FRAMEWORK_PATH."core".DIRSEP);
define("FRAMEWORK_EXT_PATH", FRAMEWORK_PATH."extensions".DIRSEP);
define("FRAMEWORK_RES_PATH", FRAMEWORK_PATH."resources".DIRSEP);
define("FRAMEWORK_MODULE_PATH", FRAMEWORK_PATH."modules".DIRSEP);

// Find Website Root
if (!defined("INDEX_FILE")) {
	if (array_key_exists("SCRIPT_FILENAME", $_SERVER))
		define("INDEX_FILE", $_SERVER["SCRIPT_FILENAME"]);
	else {
		$pos = 0;
		$stack = debug_backtrace(0);
		$fmpath_len = strlen(FRAMEWORK_PATH);
		while ($pos < count($stack)) {
			if (substr_compare($stack[$pos]['file'], FRAMEWORK_PATH, 0, $fmpath_len) !== 0) {
				define("INDEX_FILE", $stack[$pos]['file']);
				break;
			} else
				$pos++;
		}
		unset($fmpath_len);
		unset($stack);
		unset($pos);
	}
}
if(!defined("INDEX_PATH")) {
	if(defined("PATH_SUBFOLDER"))
		define("INDEX_PATH", dirname(INDEX_FILE).DIRSEP.PATH_SUBFOLDER.DIRSEP);
	else
		define("INDEX_PATH", dirname(INDEX_FILE).DIRSEP);
}

ini_set("error_log", INDEX_PATH."php.err");
define("EXT_PATH", INDEX_PATH."extensions".DIRSEP);

require FRAMEWORK_CORE_PATH."functions.inc.php";
set_include_path(FRAMEWORK_PATH.PATHSEP.INDEX_PATH);

// Request Defines
$dmn = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "unknown";
if (isset($_SERVER['HTTP_PORT']))
	define("DOMAIN", $dmn
		.($_SERVER['HTTP_PORT'] != 80 ? ":".$_SERVER['HTTP_PORT'] : ""));
else
	define("DOMAIN", $dmn);

if (preg_match("/([\w\d]+)\.(\w+)$/", $dmn, $matches)) {
	define("DOMAIN_TL", $matches[2]);
	define("DOMAIN_SL", $matches[1]);
} else {
	define("DOMAIN_TL", "");
	define("DOMAIN_SL", $dmn);
}
unset($dmn);

define("PROTOCOL_SECURE", isset($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] && 
												$_SERVER['HTTP_X_FORWARDED_PROTO'] != "http"));
define("PROTOCOL", PROTOCOL_SECURE ? "https" : "http");

$pathoffset = strlen(INDEX_PATH) - strlen($_SERVER['DOCUMENT_ROOT']);
if ($pathoffset <= 0)
	$pathoffset = 1;
define("BASE_URI", substr($_SERVER['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']), 0, $pathoffset));
$uri = substr($_SERVER['REQUEST_URI'], $pathoffset - 1);
if (($pos = strpos($uri, "?")) !== false)
	$uri = substr($uri, 0, $pos);
define("REQUEST_URI", $uri);
define("BASE_URL", PROTOCOL."://".DOMAIN.BASE_URI);
define("REQUEST_URL", BASE_URL.substr(REQUEST_URI, 1));
unset($pathoffset);
unset($uri);
unset($pos);

// Client Specific Defines
if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
	define("LEGACY_OS", true);
	define("RES_CONNECTOR", "-");
} else {
	define("LEGACY_OS", false);
	define("RES_CONNECTOR", ":");
}

// Detect Basic Browser Info
if(isset($_SERVER['HTTP_USER_AGENT'])) {
	preg_match("/(\\W)MSIE (\\d+(\\.\\d+)?);/i", $_SERVER['HTTP_USER_AGENT'], $ieVer);
	define("LEGACY_BROWSER", !preg_match('/^Mozilla\\/[5-9]\\.\\d\\s+.+\\s+(AppleWebKit|Firefox)\\/(\\d+)/i',$_SERVER['HTTP_USER_AGENT']));
	
	if(!$ieVer || !($ieVer = floatval($ieVer[1])))
		$ieVer = LEGACY_BROWSER ? 7 : 20;
	
	define("IEVERSION", $ieVer);
	unset($ieVer);
} else {
	define("LEGACY_BROWSER", true);
	define("IEVERSION", 7);
}

// Setup Custom REQUEST
$_REQUEST = array_merge($_GET, $_POST);

// Load Framework Version
define("FRAMEWORK_VERSION", trim(file_get_contents(FRAMEWORK_RES_PATH."version")));

// Load Error Handling Hooks
require FRAMEWORK_CORE_PATH."error-handling.inc.php";

// Process Framework Mode
if (!defined("FRAMEWORK_MODE"))
	define("FRAMEWORK_MODE", "website");

$loaderPath = FRAMEWORK_CORE_PATH."loaders".DIRSEP.FRAMEWORK_MODE.".inc.php";
if (is_file($loaderPath))
	require $loaderPath;
else
	throw new Exception("Missing Loader for Mode `".FRAMEWORK_MODE."`");
?>
