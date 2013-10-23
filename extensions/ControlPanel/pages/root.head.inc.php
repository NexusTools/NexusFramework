<?php
Template::reset();

Template::setRobotsPolicy(false);
Template::setMetaTag("keywords", "nexustools, framework, php, controlpanel");
Template::setMetaTag("description", "The NexusTools PHP Framework Official ControlPanel");
$base = dirname(dirname(__FILE__)).DIRSEP;
$owdir = getcwd();
chdir($base);
Template::addScript($base."cp-script.js");
Template::addStyles(Array(FRAMEWORK_PATH."resources/stylesheets/widgets.css", $base."cp-style.css"));
if (array_key_exists("popup", $_GET))
	Template::addStyle($base."cp-popup.css");

$externStyle = fullpath("cp-theme.css");
if (file_exists($externStyle))
	Template::addStyle($externStyle);

requireAddon("eventable-object");
requireAddon("unfinished-work");
requireAddon("file-upload");

Template::setTitleFormat("{{PAGENAME}} [ControlPanel]");
Template::setTitle("Select a Section");

PageModule::setThemePath($base."theme");
chdir($owdir);
?>
