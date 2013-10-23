<?php
switch ($mode) {
case EditCore::RENDER:
	echo "<input class=\"text large\" name=\"$name\" value=\"";
	echo htmlspecialchars($value);
	echo "\"";
	if (isset($meta['readonly']))
		echo " readonly";
	echo " />";
	break;

case EditCore::VALIDATE:
	if (!$value || !strlen($value))
		return "Required";

	if (strlen($value) < 2)
		return "Too short, 2 to 40 characters.";

	if (strlen($value) > 40)
		return "Too long, 2 to 40 characters.";

	return true;
}
?>
