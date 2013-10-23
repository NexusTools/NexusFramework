<?php
//ControlPanel::registerPage("Resources", "Active", "edit/active.inc.php", true, 0, 0);
ControlPanel::registerPage("Resources", "Media", "edit/media.inc.php", true, 0, 0);
ControlPanel::registerPage("Resources", "Delete", "edit/delete.inc.php", false);

return;
ControlPanel::registerPage("Resources", "Document Root", "edit/document-root.inc.php", true, 1);
if (!startsWith(FRAMEWORK_PATH, INDEX_PATH))
	ControlPanel::registerPage("Resources", "Framework", "edit/framework.inc.php", true, 1);
?>
