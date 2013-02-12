<?php
ControlPanel::registerPage("Pages", "Create", "edit/create.inc.php", true, 0);
ControlPanel::registerPage("Pages", "Manage", "edit/manage.inc.php", true, 0);

ControlPanel::registerPage("Pages", "Edit", "edit/edit.inc.php", false);
ControlPanel::registerPage("Pages", "Edit Widgets", "edit/edit-widgets.inc.php", false);
ControlPanel::registerPage("Pages", "Edit Widget", "edit/edit-widget.inc.php", false);
ControlPanel::registerPage("Pages", "Delete Widget", "edit/delete-widget.inc.php", false);
ControlPanel::registerPage("Pages", "Create Widget", "edit/create-widget.inc.php", false);

ControlPanel::registerPage("Pages", "Delete", "edit/delete.inc.php", false);

EditCore::registerEditor("layout", "editors/layout.inc.php");
?>
