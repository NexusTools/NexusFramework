<?php
switch (VirtualPages::getMode()) {
case VirtualPages::CREATE:
	return Array("source" => "New BBC Widget");
	break;

case VirtualPages::RENDER:
	$config = VirtualPages::getArguments();
	User::runAsSystem("BBCEngine::render", Array($config['source']));
	break;

case VirtualPages::RENDER_EDITOR:
	$config = VirtualPages::getArguments();
	echo "source<br /><textarea code=\"bbc\" style=\"height: 400px; width: 100%;\" name=\"source\">";
	echo htmlentities($config['source']);
	echo "</textarea>";
	break;

case VirtualPages::UPDATE_CONFIG:
	return Array("source" => $_POST["source"]);

}
?>
