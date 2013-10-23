<?php
User::registerExtension("TimeZoneUser");

function __tzUser_userChangeTriggerCallback($module, $event, $arguments) {
	if ($module == "User" && $event == "Changed")
		date_default_timezone_set(User::getTimeZone());
}

Triggers::watchModule("User", "__tzUser_userChangeTriggerCallback");
date_default_timezone_set(User::getTimeZone());
?>
