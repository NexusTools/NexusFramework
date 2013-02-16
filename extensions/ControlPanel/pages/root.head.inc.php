<?
Template::reset();


Template::setRobotsPolicy(false);
Template::setMetaTag("keywords", "nexustools, framework, php, controlpanel");
Template::setMetaTag("description", "The NexusTools PHP Framework Official ControlPanel");
$base = dirname(dirname(__FILE__)) . DIRSEP;
$owdir = getcwd();
chdir($base);
Template::addScript($base . "cp-script.js");
Template::addStyle(FRAMEWORK_PATH . "resources/stylesheets/widgets.css");
Template::addStyle($base . "cp-style.css");
if(array_key_exists("popup", $_GET))
	Template::addStyle($base . "cp-popup.css");

requireAddon("eventable-object");
requireAddon("unfinished-work");
requireAddon("file-upload");

Template::setTitleFormat("{{PAGENAME}} (NexusFramework ControlPanel)");
Template::setTitle("Select a Section");

PageModule::setThemePath($base . "theme");
chdir($owdir);
?>
