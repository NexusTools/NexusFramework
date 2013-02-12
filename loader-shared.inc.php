<?php define("FRAMEWORK_VERSION", "3.6 beta 3");

if(defined("INDEX_PATH"))
	return;
	
if(floatval(phpversion()) < 5.3)
	throw new Exception("PHP 5.3 or Higher Required.");

define("NATIVE_OB_LEVEL", ob_get_level());
if(!ob_get_level())
    ob_start();
function __frameworkInternal__ignoreOutput(){return "";}
ob_start("__frameworkInternal__ignoreOutput");
error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set('UTC');

define("LEGACY_OS", strncasecmp(PHP_OS, 'WIN', 3) == 0);
define("RES_CONNECTOR", LEGACY_OS ? "-" : ":");
define("FRAMEWORK_PATH", dirname(__FILE__) . DIRECTORY_SEPARATOR);
define("START_TIME", microtime(true));
define("DIRSEP", DIRECTORY_SEPARATOR);
define("PATHSEP", PATH_SEPARATOR);

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
define("FRAMEWORK_EXT_PATH", FRAMEWORK_PATH . "extensions" . DIRSEP);

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

define("PROTOCOL", isset($_SERVER['HTTPS']) ? "https" : "http");

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

$_REQUEST = array_merge($_GET, $_POST);
require dirname(__FILE__) . DIRSEP . "error-handling.inc.php";
require dirname(__FILE__) . DIRSEP . "functions.inc.php";
set_include_path(FRAMEWORK_PATH . PATHSEP . INDEX_PATH);

define("LEGACY_BROWSER", isset($_SERVER['HTTP_USER_AGENT']) && 
        preg_match('/(?i)msie [1-9]/',$_SERVER['HTTP_USER_AGENT']));
?>
