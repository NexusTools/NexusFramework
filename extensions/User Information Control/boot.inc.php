<?php
function __userInformation__ControlPanelWatcher($module, $event, $arguments) {
	switch ($event) {
	case "Database Entry Actions":
		if ($arguments[0] == "Users" && $arguments[1] == "Manage")
			return Array("Contact" => "Users/Contact?id={{rowid}}");
		break;
	}
}

Triggers::watchModule("ControlPanel", "__userInformation__ControlPanelWatcher");
ControlPanel::registerPage("Users", "Contact", "edit/edit-user-contact.json", false);
?>
