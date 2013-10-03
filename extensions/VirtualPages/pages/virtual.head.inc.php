<?php
$page = PageModule::getValue("page");
VirtualPages::runPage($page['type'], VirtualPages::HEADER, $page);
VirtualPages::runWidgetHeaders($page['rowid']);
Template::setTitle($page['title']);
?>
