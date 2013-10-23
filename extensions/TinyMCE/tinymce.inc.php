<?php
switch ($mode) {
case EditCore::RENDER: // style=\"width: 350px;\"
	echo "<textarea code=\"html\" style=\"width: 100%; height: 400px;\" name=\"$name\">";
	echo htmlspecialchars($value);
	echo "</textarea>";
	break;
}
?>
