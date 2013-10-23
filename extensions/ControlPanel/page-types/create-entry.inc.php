<?php

$default_buttons = Array("create");
if (isset($definition['publishable']))
	$default_buttons[] = "publish";
$default_buttons[] = "discard";

$buttons = Array();
foreach ($default_buttons as $btn) {
	if (isset($definition['actions'][$btn]))
		$buttons[$btn] = $definition['actions'][$btn];
}

return "<?php ControlPanel::renderEditPage($definition[database], \"$definition[table]\", -1, ".to_php($definition['actions']).
	", ".to_php($definition['fields']).", ".to_php($buttons)."); ?>";
?>
