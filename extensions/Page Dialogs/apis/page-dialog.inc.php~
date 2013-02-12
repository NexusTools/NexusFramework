<?php

$module = new PageModule(API::getCurrentArugment());
$module->initialize(false);
$module->run(true, true);

return Array("html" => $module->getHTML(), "title" => Template::getTitle());
?>
