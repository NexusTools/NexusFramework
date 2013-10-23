<?php
$module = new PageModule(API::getCurrentArugment());
$module->initialize(false);
$theme = $module->getTheme();
$theme->initialize();
$module->run(true);
$data = Array("html" => $module->getHTML(), "title" => Template::getTitle());

global $__bufferCapture;
$__bufferCapture = "";
function __captureOB($data, $phase) {
	global $__bufferCapture;
	if ($phase == PHP_OUTPUT_HANDLER_START)
		$__bufferCapture = "";
	else
		if ($phase == PHP_OUTPUT_HANDLER_CONT)
			$__bufferCapture .= $data;
		else
			return false;
	return "";
}
ob_start("__captureOB");
$theme->runHeader();
ob_end_flush();
$data['theme'] = Array("header" => $__bufferCapture);
return $data;
?>
