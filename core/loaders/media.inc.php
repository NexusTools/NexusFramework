x<?php
require dirname(__FILE__).DIRECTORY_SEPARATOR."loader-shared.inc.php";

define("DEV_MODE", false);
define("DEBUG_MODE", false);
define("NOACCESS_MODE", true);
define("MEDIA_PATH", INDEX_PATH);
define("MEDIA_URI", BASE_URI);
define("MEDIA_URL", BASE_URL);

if (is_file(INDEX_PATH."framework.config.php"))
	require INDEX_PATH."framework.config.php";

require "core/ClassLoader.class.php";
OutputHandlerStack::init();
Framework::serveMediaFile(cleanpath(INDEX_PATH.REQUEST_URI));
?>
