<?php
switch ($mode) {
case EditCore::RENDER:
	echo "<input class=\"text\" name=\"$name\" value=\"";
	echo htmlspecialchars($value);
	echo "\" style=\"width: 350px\" />";
	break;
}
?>
