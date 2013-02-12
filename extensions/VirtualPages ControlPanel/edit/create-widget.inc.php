<?php
$_GET['id'] = VirtualPages::createWidget($_GET['type'], $_GET['location'], $_GET['slot'], $_GET['parent'], $_GET['section']);
ControlPanel::changePage("Edit Widget");
?>
