<?php
switch ($mode) {
case EditCore::RENDER:
	echo "<input class=\"text\" type=\"password\" name=\"$name\" value=\"";
	echo htmlspecialchars($value);
	echo "\" style=\"width: 350px\" /><br />Retype Password<br />";
	echo "<input class=\"text\" type=\"password\" name=\"__retype_$name\" value=\"";
	if ($value && isset($_POST["__retype_$name"]))
		echo htmlspecialchars($_POST["__retype_$name"]);
	echo "\" style=\"width: 350px\" />";
	break;

case EditCore::VALIDATE:
	if (!$value || !strlen($value))
		return "Required";

	//bubbles@domain.com
	if (!isset($_POST["__retype_$name"]))
		return "Re-Type Required";

	if ($_POST["__retype_$name"] != $value)
		return "Didn't Match";
	break;

case EditCore::ENCODE:
	return md5($value, true);
}
?>
