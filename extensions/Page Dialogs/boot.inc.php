<?php
Template::addScript("script.js");
Framework::registerCustomTag("popup");
Framework::registerCustomTag("close");
Framework::registerCustomTag("popupdarkoverlay");
if(LEGACY_BROWSER || preg_match('/Opera/i', $_SERVER['HTTP_USER_AGENT']))
    Template::addStyle("popup.legacy.css");
else
    Template::addStyle("popup.css");

function preloadPopup($path){
    $module = new PageModule($path);
    $module->run(true, true);
    echo "<div data-page-popup-preload='$path' style='display: none; width: 0px; height: 0px; position: absolute; '>";
    echo $module->getHTML();
    echo "</div>";
}
?>
