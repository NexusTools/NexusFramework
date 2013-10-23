<?php
switch ($mode) {
case EditCore::RENDER:
	echo "<input type=\"condition\" class=\"text\" name=\"$name\" value=\"";
	echo htmlspecialchars($value);
	echo "\" style=\"width: 350px\" />";
	break;

case EditCore::VALIDATE:
	if ($meta['required'] && !trim($value))
		return "Required";
	return false;
	break;
}
?>
