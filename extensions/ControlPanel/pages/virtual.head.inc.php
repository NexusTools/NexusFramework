<?php
include dirname(__FILE__).DIRSEP."root.head.inc.php";
Template::setTitle(PageModule::getArgument(2)." | ".PageModule::getArgument(1));
Triggers::broadcast("ControlPanel", "Header", Array(PageModule::getArgument(1), PageModule::getArgument(2)));
?>
