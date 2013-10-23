<?php
VirtualPages::registerPageType("form-forwarder", "form-page.inc.php");
$__formForwarder__lastEntryName = "";
function FormEntryAttr($name) {
	global $__formForwarder__lastEntryName;
	$__formForwarder__lastEntryName = $name;
	if (isset($_POST[$name]))
		return "name=\"$name\" value=\"".htmlspecialchars($_POST[$name])."\"";

	return "name=\"$name\"";
}
function FormSelectOption($value, $default = false) {
	global $__formForwarder__lastEntryName;
	if (!isset($_POST[$__formForwarder__lastEntryName])) {
		if ($default)
			return "value=\"".htmlspecialchars($value)."\" selected";
		return "value=\"".htmlspecialchars($value)."\"";
	} else
		if ($_POST[$__formForwarder__lastEntryName] == $value)
			return "value=\"".htmlspecialchars($value)."\" selected";
		else
			return "value=\"".htmlspecialchars($value)."\"";
}
?>
