<?php
switch (VirtualPages::getMode()) {
case VirtualPages::CREATE:
	return Array("raw" => "/* New Style Rules - Add !important To Override Theme */", "compressed" => "");
	break;

case VirtualPages::HEADER:
	$config = VirtualPages::getArguments();
	Template::addHeaderElement("style", Array(), StyleCompressor::processContent($config['compressed'], false, true));
	break;

case VirtualPages::RENDER_EDITOR:
	$config = VirtualPages::getArguments();
	echo "<textarea code=\"css\" style=\"height: 400px; width: 100%;\" name=\"style\" class=\"width: 100%;\">";
	echo htmlentities($config['raw']);
	echo "</textarea>";
	break;

case VirtualPages::UPDATE_CONFIG:
	$widget = VirtualPages::getArguments();
	return Array("raw" => $_POST["style"], "compressed" => StyleCompressor::compress($_POST["style"]));

}
?>
