<?php

if (isset($_POST['pass'])) {
	if ($_POST['pass'] != $_POST['rpass'])
		echo "<banner class='error'>Passwords didn't match</banner>";
	else
		if (RootUser::instance()->setPassword($_POST['pass'])) {
			echo "<banner class='success'>Root Password Changed</banner>";
			ControlPanel::changePage("Configure");
			return;
		} else
			echo "<banner class='error'>An internal error occured, password could not be set.</banner>";
}
?><pagebuttons>
<?php
ControlPanel::renderStockButton("apply");
ControlPanel::renderStockButton("discard", "ControlPanel::loadPage('Website', 'Configure')");
?>
</pagebuttons>
<form method="" action="control://Website/Change Root Password">New Password<br />
<input style="width: 350px" name="pass" type="password" class="text" /><br />
Retype New Password<br />
<input style="width: 350px" name="rpass" type="password" class="text" /></form><?php ?>
