<?php
switch (VirtualPages::getMode()) {
case VirtualPages::CREATE:
	return Array("html" => "<p>New HTML Widget</p>", "cond" => "");
	break;

case VirtualPages::RENDER:
	$config = VirtualPages::getArguments();
	if (!strlen($config['cond']) || Runtime::evaluate($config['cond']))
		echo interpolate($config['html'], true, Triggers::broadcast("template", "page-data"));
	break;

case VirtualPages::RENDER_EDITOR:
	$config = VirtualPages::getArguments();
	echo "Condition<br /><input style='width: 350px' type='text' class='text' value='";
	echo htmlentities($config['cond']);
	echo "' name='cond' /><br />";
	echo "Content<br /><textarea code=\"html\" style=\"height: 400px; width: 100%;\" name=\"html\">";
	echo htmlentities($config['html']);
	echo "</textarea>";
	break;

case VirtualPages::UPDATE_CONFIG:
	return Array("html" => $_POST["html"], "cond" => $_POST["cond"]);

}
?>
