<?php
$otherUser = User::fetch($_GET['id']);
if (!$otherUser->isValid()) {
	echo "Invalid User";
	return;
}
if ($otherUser->getID() != User::getID() && $otherUser->getLevel() >= User::getLevel())
	echo "You do not have permission to change this user's password.";
else {
	if (isset($_POST['pass'])) {
		if ($_POST['pass'] != $_POST['rpass'])
			echo "<banner class='error'>Passwords didn't match</banner>";
		else
			if ($otherUser->setPassword($_POST['pass'])) {
				echo "<banner class='success'>Updated Password for ".$otherUser->getFullName()."</banner>";
				ControlPanel::changePage("Manage");
				return;
			} else
				echo "<banner class='error'>An internal error occured, password could not be set.</banner>";
	}
?><pagebuttons>
    <?php
	ControlPanel::renderStockButton("apply");
	ControlPanel::renderStockButton("discard", "ControlPanel::loadPage('Users', 'Manage')");
?>
    </pagebuttons>
    <form method="" action="control://Users/Set User Password?id=<?php echo $otherUser->getID(); ?>">New Password<br />
    <input style="width: 350px" name="pass" type="password" class="text" /><br />
    Retype New Password<br />
    <input style="width: 350px" name="rpass" type="password" class="text" /></form><?php
}
?>
