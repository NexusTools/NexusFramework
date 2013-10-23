<pagebuttons><?php
$settings = new Settings("Google Analytics");
if (isset($_POST['action'])) {
	$settings->setValue("code", $_POST['code']);
	$settings->setValue("domain", $_POST['domain']);
}

ControlPanel::renderStockButton("apply");
ControlPanel::renderStockButton("discard");
?></pagebuttons>
<form action="control://Website/Google Analytics">
Tracking Code<br />
<input class="text large" value="<?php echo htmlspecialchars($settings->getValue("code")); ?>" name="code" /><br />
Default Domain <help title="When tracking multiple top level domains, set this to the default domain">?</help><br />
<input value="<?php echo htmlspecialchars($settings->getValue("domain")); ?>" class="text" style="width: 350px" name="domain" /></form>
