<?php
switch ($mode) {
case EditCore::RENDER:
	if (is_object($meta['database']))
		$database = $meta['database'];
	else
		$database = eval("return $meta[database];");

	$entries = $database->selectRecursive($meta['table']);

	echo "<select style=\"width: 350px; font-family: monospace, courier new\" name=\"$name\" value=\"";
	echo htmlspecialchars($value);
	echo "\">";

	ControlPanel::renderRecursiveSelectOptions($entries, $value === false ? 0 : $value, array_key_exists("show-all", $meta) ? -1 : $meta['rowid']);
	echo "</select>";
}
?>
