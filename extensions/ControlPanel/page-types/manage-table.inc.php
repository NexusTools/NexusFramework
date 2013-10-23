<?php
if (isset($definition['sortField']))
	$sortField = $definition['sortField'];
else {
	$sortField = false;
	foreach ($definition['fields'] as $key => $val) {
		if ($key == "icon" || $key == "rowid")
			continue;

		$sortField = $key;
		break;
	}
}
return "<?php\nControlPanel::renderManagePage($definition[database], \"$definition[table]\", ".to_php($definition['fields']).
	", ".to_php($definition['actions']).", ".(isset($definition['publishable']) ? "true" : "false").
	", ".to_php($definition['buttons']).", \"$sortField\", "
	.(isset($definition['hasParenting']) ? "true" : "false").");\n?>";
?>
