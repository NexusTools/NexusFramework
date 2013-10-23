<?php
class PageDialogs {

	private static $preinstalled = Array();

	public static function dumpPreinstalledDialogs() {
		foreach (self::$preinstalled as $page => $val) {
			echo "<popup class='preload hidden' preload-page='".htmlspecialchars($page)."'><close onclick='closeLastPopup()'>X</close><contents>";
			$module = new PageModule($page);
			echo $module->getHTML(true);
			echo "</contents></popup>";
		}
	}

	public static function preinstall($page) {
		self::$preinstalled[relativepath($page)] = true;
		Template::addFooter(Array(__CLASS__, "dumpPreinstalledDialogs"));
	}

}
?>
