<?php
function __downloadPages_fileServerEvent($module, $event, $arguments) {
	if ($module == "FileServer")
		switch ($event) {
		case "ServeFile":
			if ((!array_key_exists("key", $_GET) || $_GET['key'] != $_SESSION['file-serve-key']) && (array_key_exists("info", $_GET) || startsWith(REQUEST_URI, "/downloads/"))) {

				$_SESSION['file-serve-key'] = Framework::uniqueHash(false, Framework::HexEncodedHash);
				$module = new PageModule("/downloads/serve-file");
				$module->initialize(false);
				$theme = $module->getTheme();
				$theme->initialize();
				if ($module->hasError())
					return;

				$outputCapture = new OutputCapture();
				if (DEBUG_MODE) {
					if (isset($_GET['dumpstate'])) {
						Framework::dumpState();
						exit();
					}

					Profiler::start("Template");
				}

				if (Framework::isHeadRequest())
					exit;

				Template::writeHeader();
				echo "<framework:theme>";
				$module->getTheme()->runHeader();
				echo "<framework:page>";
				Triggers::broadcast("Template", "ServePage", "header");
				$module->run();
				Triggers::broadcast("Template", "ServePage", "footer");
				echo "</framework:page>";
				$module->getTheme()->runFooter();
				echo "</framework:theme>";
				Template::writeFooter();
				$outputCapture->serve();
			}
			unset($_SESSION['file-serve-key']);
			break;
		}
}

Triggers::watchModule("FileServer", "__downloadPages_fileServerEvent");
?>
