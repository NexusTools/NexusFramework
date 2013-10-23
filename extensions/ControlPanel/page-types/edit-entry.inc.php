<?php

$default_buttons = Array("save");
if (isset($definition['id'])) {
	$id = $definition['id'];
	$default_buttons[] = "save-close";
	// TODO: Implement Publish/Revoke
	$default_buttons[] = "discard";
} else {
	$id = '$_GET["id"]';
	$default_buttons[] = "clone";
	$default_buttons[] = "save-close";
	// TODO: Implement Publish/Revoke
	$default_buttons[] = "discard";
	$default_buttons[] = "delete";
}

$buttons = Array();
foreach ($default_buttons as $btn) {
	if (isset($definition['actions'][$btn]))
		$buttons[$btn] = $definition['actions'][$btn];
}

return "<?php ControlPanel::renderEditPage($definition[database], \"$definition[table]\", $id, ".to_php($definition['actions']).
	", ".to_php($definition['fields']).", ".to_php($buttons)."); ?>";
?>
