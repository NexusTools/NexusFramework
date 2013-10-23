<?php
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
	case "create":
		if (!trim($_POST['name'])) {
			echo "<banner class=\"error\">Name Required.</banner>";
			continue;
		}
		$_POST['name'] = trim($_POST['name']);
		$_GET['id'] = MailCenter::createTemplate($_POST['name'], $_POST['text'], $_POST['html']);
		echo "<banner class=\"success\">Template Created</banner>";
		$_POST = Array();
		ControlPanel::changePage("Edit Template");
		return;
	}
}
?><pagebuttons><?php
ControlPanel::renderStockButton("create");
?></pagebuttons><form action="control://Mail Center/Create Template" method="POST">
Name:<br /><?php
EditCore::render("line", "name");
?><br />
HTML Version:<br />
<?php EditCore::render("html", "html"); ?><br />
Text Version:<br />
<?php EditCore::render("text", "text"); ?></form>
