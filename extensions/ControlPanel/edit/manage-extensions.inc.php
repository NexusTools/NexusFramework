<?php
ControlPanel::renderManagePage(ExtensionDatabase::getInstance(), "extensions", Array("name" => Array(
	"value" => "{{name}}\n{{small}}{{description}}{{endsmall}}"
), "provides", "version", "author"), Array(
	"Edit" => "Extensions/Edit?id={{rowid}}",
	"Delete" => "Extensions/Delete?id={{rowid}}"
), true, Array(
	"create" => "ControlPanel.loadPage(\"Extensions\", \"Create\")"
));
?>
