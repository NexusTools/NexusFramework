<?php
require dirname(__FILE__) . DIRECTORY_SEPARATOR . "loader-shared.inc.php";

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

define("NOACCESS_MODE", true);
define("DEBUG_MODE", false);

if(is_file(INDEX_PATH . "framework.config.php"))
	require INDEX_PATH . "framework.config.php";

require "core/ClassLoader.class.php";
OutputHandlerStack::init();
Framework::serveResource(substr(REQUEST_URI, 1), true);
?>
