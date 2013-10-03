<?php

$module = new PageModule(API::getCurrentArugment());
$module->initialize(false);

return Array("html" => $module->getHTML(true), "title" => Template::getTitle());
?>
