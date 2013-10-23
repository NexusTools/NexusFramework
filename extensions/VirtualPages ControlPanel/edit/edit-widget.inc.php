<form action="control://Pages/Edit Widget"><?php
$widget = VirtualPages::fetchWidget($_GET['id']);
if (isset($_POST['action']) && ($_POST['action'] == "save" || $_POST['action'] == "save-close")) {
	$widget['config'] = VirtualPages::runWidget($widget['type'], VirtualPages::UPDATE_CONFIG, $_GET['id']);
	if (!isset($widget['config']['title']))
		$widget['config']['__widget_name'] = $_POST['__widget_name'];
	if (VirtualPages::updateWidget($_GET['id'], $widget['config']))
		echo "<banner class=\"success\">Widget Updated Successfully!</banner>";

	if ($_POST['action'] == "save-close") {
		ControlPanel::changePage("Edit Widgets");
		return;
	}
}

echo "<h3>Editing `";
echo StringFormat::displayForID($widget['type']);
echo "` Widget</h3>";

if (!isset($widget['config']['title'])) {
	echo "Widget Name <help title=\"Used for Reference only. Leave blank to disable.\">?</help><br />";
	echo "<input class=\"text\" style=\"width: 350px\" value=\"";
	if (isset($widget['config']['__widget_name']))
		echo htmlspecialchars($widget['config']['__widget_name']);
	echo "\" name=\"__widget_name\" /><br />";
}

VirtualPages::runWidget($widget['type'], VirtualPages::RENDER_EDITOR, $widget['config'], $widget['rowid']);
?></form><pagebuttons><?php

$oldstate = "id: ".$_GET['id'].", subtitle: \"".(isset($_GET['subtitle']) ? $_GET['subtitle'] : "")
	."\", title: \"".(isset($_GET['title']) ? $_GET['title'] : "Unknown")
	."\", location: ".(isset($_GET['location']) ? $_GET['location'] : $widget['location'])
	.", section: \"".(isset($_GET['section']) ? $_GET['section'] : $widget['section'])
	."\", parent: ".(isset($_GET['parent']) ? $_GET['parent'] : $widget['parent'])
	.", slot: ".(isset($_GET['slot']) ? $_GET['slot'] : $widget['slot']);

ControlPanel::renderStockButton("save", "ControlPanel.submitForm(this, {".$oldstate."})");
ControlPanel::renderStockButton("save-close", "ControlPanel.submitForm(this, {".$oldstate."})");
ControlPanel::renderStockButton("discard", "ControlPanel.loadPage('Pages', 'Edit Widgets', {".$oldstate."})");
?></pagebuttons>
