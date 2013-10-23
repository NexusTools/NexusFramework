<?php
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
	case "new":
		$user = User::fetch($_POST['user']);
		if ($user->isValid() && UserGroups::getDatabase()->insert("members", Array("group" => $_GET['group'], "user" => $user->getID()))) {
			echo "<banner class=\"success\">User `";
			echo $user->getDisplayName();
			echo "` Added</banner>";
			ControlPanel::changePage("Group Members");
			$_GET = Array("id" => $_GET['group']);
			$_POST = Array();
			return;
		} else
			echo "No Such User Exists";
		return;
	}
}
?><pagebuttons><?php
ControlPanel::renderStockButton("new", "ControlPanel.submitForm(this);", "Add Member");
?></pagebuttons>
<form action="control://Users/Add Group Member?group=<?php echo $_GET['group']; ?>" method="POST">Username, ID or Email<br />
<input name="user" class="text large" /></form>
<?php
return Array(false, Array("title" => "Groups", "action" => "ControlPanel.loadPage('Users', 'Groups');"), Array("title" => User::getGroupNameForID($_GET['group']), "action" => "ControlPanel.loadPage('Users', 'Group Members', {id: ".$_GET['group']."});"), "Add Member");
?>
