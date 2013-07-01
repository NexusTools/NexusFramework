<?php
class PageDialogs {

    private static $preinstalled = Array();

    public static function dumpPreinstalledDialogs(){
        foreach(self::$preinstalled as $page => $val) {
            echo "<popup class='preload hidden' preload-page='" . htmlspecialchars($page). "'><close onclick='closeLastPopup()'>X</close><contents>";
            $module = new PageModule($page);
			$module->initialize(false);
			$module->run();
            echo "</contents></popup>";
        }
    }

    public static function preinstall($page){
        self::$preinstalled[relativepath($page)] = true;
        Template::addFooter(Array(__CLASS__, "dumpPreinstalledDialogs"));
    }

}
?>
