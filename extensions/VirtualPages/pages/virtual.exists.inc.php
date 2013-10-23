<?php
$page = VirtualPages::fetchPage(PageModule::getWorkingPath(), true);
if ($page) {
	PageModule::setValue("page", $page);
	return true;
}

return false;
?>
