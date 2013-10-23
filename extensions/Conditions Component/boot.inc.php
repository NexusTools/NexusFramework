<?php

function __conditionComponent__callback($module, $section, $args) {
	if ($module != "ControlPanel" || $section != "Header")
		return;

	Template::addScript(__DIR__.DIRSEP."condition-editor.js");
}

Triggers::watchModule("ControlPanel", "__conditionComponent__callback");
?>
