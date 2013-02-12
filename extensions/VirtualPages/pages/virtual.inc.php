<?php
$page = PageModule::getValue("page");
VirtualPages::runPage($page['type'], VirtualPages::RENDER, $page);
?>
