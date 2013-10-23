<?php

ControlPanel::registerPage("Users", "Create", "edit/create-user.json", true, 0, 1, true);
ControlPanel::registerPage("Users", "Manage", "edit/manage-users.json", true, 0);
ControlPanel::registerPage("Users", "Edit User", "edit/edit-user.json", false);
ControlPanel::registerPage("Users", "Set User Password", "edit/set-user-password.inc.php", false);
?>
