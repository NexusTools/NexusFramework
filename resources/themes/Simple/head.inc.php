<?php
//Template::addStyle(FRAMEWORK_RES_PATH . "stylesheets" . DIRSEP . "widgets.css");
Template::addExternalStyle("//fonts.googleapis.com/css?family=Ubuntu");
Template::addStyle("styles".DIRSEP."simple.css");

$footer = Triggers::broadcast("Menus", "Footer");
if (is_array($footer) && count($footer) > 0) {
	if (!function_exists("__basicTheme__dumpFooterMenu")) {
		function __basicTheme__dumpFooterMenu($menu) {
			echo "<ul>";
			foreach ($menu as $entry) {
				echo "<li>";
				$url = $entry['url'];
				$text = $entry['text'];
				
				if($url) {
					if (startsWith($url, "http"))
						$target = " target=\"_blank\"";
					else
						$target = "";
					echo "<a$target href=\"".htmlspecialchars($url).
						"\">".htmlentities($text)."</a>";
				} else
					echo "<span>".htmlentities($text)."</span>";
				
				if(array_key_exists("submenu", $entry) && $entry['submenu'])
					__basicTheme__dumpFooterMenu($entry['submenu']);
				echo "</li>";
			}
			echo "</ul>";
		}
	}

	Template::addStyle(__DIR__.DIRSEP."styles".DIRSEP."footer.css");
	PageModule::setValue("Template-Footer", $footer);
}
?>
