<?php
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
	case "save-close":
	case "save":
		if (!trim($_POST['name'])) {
			echo "<banner class=\"error\">Name Required.</banner>";
			break;
		}
		$_POST['name'] = trim($_POST['name']);
		if (!MailCenter::getDatabase()->update("templates", Array("name" => $_POST['name']), Array("rowid" => $_GET['id']))) {
			echo "<banner class=\"error\">Unable to update name.</banner>";
			break;
		}
		if (file_put_contents(MailCenter::getStoragePath("templates".DIRSEP.$_GET['id'])."payload.txt", $_POST['text']) === false) {
			echo "<banner class=\"error\">Unable to write Text Payload.</banner>";
			break;
		}
		if (file_put_contents(MailCenter::getStoragePath("templates".DIRSEP.$_GET['id'])."payload.html", $_POST['html']) === false) {
			echo "<banner class=\"error\">Unable to write HTML Payload.</banner>";
			break;
		}

		echo "<banner class=\"success\">Template Saved</banner>";
		if ($_POST['action'] == "save-close") {
			ControlPanel::changePage("Templates");
			return;
		} else
			break;
	}
}
?><pagebuttons><?php
ControlPanel::renderStockButton("save");
ControlPanel::renderStockButton("save-close");
ControlPanel::renderStockButton("discard");
?></pagebuttons><form action="control://Mail Center/Edit Template?id=<?php echo $_GET['id']; ?>" method="POST">
Name:<br /><?php
EditCore::render("line", "name", isset($_POST['name']) ? $_POST['name'] : MailCenter::nameForTemplateID($_GET['id']));
?><br />
HTML Version:<br />
<?php EditCore::render("html", "html", isset($_POST['html']) ? $_POST['html'] : file_get_contents(MailCenter::getStoragePath("templates".DIRSEP.$_GET['id'])."payload.html")); ?><br />
Text Version:<br />
<?php EditCore::render("text", "text", isset($_POST['text']) ? $_POST['text'] : file_get_contents(MailCenter::getStoragePath("templates".DIRSEP.$_GET['id'])."payload.txt")); ?></form>
