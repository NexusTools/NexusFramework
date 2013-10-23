<?php
$widget = VirtualPages::fetchWidget(API::getCurrentArgument());
if (!$widget)
	return Array("error" => "No Such Widget");

$capture = new OutputCapture(true);
VirtualPages::runWidget($widget['type'],
	VirtualPages::RENDER_EDITOR,
	unserialize($widget['config']));
$capture->finish();
return Array("html" => $capture->getOutput());
?>
