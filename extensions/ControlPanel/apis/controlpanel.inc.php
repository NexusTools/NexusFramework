<?php
$args = Framework::splitPath(API::getCurrentArugment());
$data = ControlPanel::run($args[0], count($args) > 1 ? $args[1] : "");
if (!count($data['tools']))
	unset($data['tools']);
return $data;
?>
