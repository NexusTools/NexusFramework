<?php
//Template::addStyle(FRAMEWORK_RES_PATH . "stylesheets" . DIRSEP . "widgets.css");
Template::addExternalStyle("//fonts.googleapis.com/css?family=Ubuntu");
Template::addStyle("styles".DIRSEP."simple.css");

$footer = Triggers::broadcast("Template", "Footer");
if (is_array($footer) && count($footer) > 0) {
	if (!function_exists("__basicTheme__dumpFooterMenu")) {
		function __basicTheme__dumpFooterMenu($menu) {
			echo "<ul>";
			foreach ($menu as $text => $url) {
				if (is_numeric($text)) {
					$text = $url;
					$url = "/".StringFormat::idForDisplay($text);
				}

				echo "<li>";
				if (is_array($url)) {
					Template::addScript(__DIR__.DIRSEP."scripts".DIRSEP."footer.js");
					echo "<span>";
					echo htmlentities($text);
					echo "</span>";

					__basicTheme__dumpFooterMenu($url);
				} else {
					if (startsWith($url, "http"))
						$target = " target=\"_blank\"";
					else
						$target = "";
					echo "<a$target href=\"".htmlspecialchars($url).
						"\">".htmlentities($text)."</a>";
				}
				echo "</li>";
			}
			echo "</ul>";
		}
	}

	Template::addStyle(__DIR__.DIRSEP."styles".DIRSEP."footer.css");
	PageModule::setValue("Template-Footer", $footer);
}
?>
