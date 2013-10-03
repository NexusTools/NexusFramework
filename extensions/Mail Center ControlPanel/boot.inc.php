<?php
ControlPanel::registerPage("Mail Center", "Compose", "edit/send email.inc.php", true, 0);
ControlPanel::registerPage("Mail Center", "Outbox", "edit/emails.json", true, 2);
ControlPanel::registerPage("Mail Center", "View Email", "edit/view-email.inc.php", false);

ControlPanel::registerPage("Mail Center", "Templates", "edit/templates.json");
ControlPanel::registerPage("Mail Center", "Create Template", "edit/create-template.inc.php", false);
ControlPanel::registerPage("Mail Center", "Edit Template", "edit/edit-template.inc.php", false);

ControlPanel::registerPage("Mail Center", "Campaigns", "edit/campaigns.json");

ControlPanel::registerPage("Mail Center", "Mailing Lists", "edit/mailing-lists.json");
ControlPanel::registerPage("Mail Center", "Create Mailing List", "edit/create-mailing-list.inc.php", false);
?>
