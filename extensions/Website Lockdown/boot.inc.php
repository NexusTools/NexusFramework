<?php

if (defined("LOCKDOWN_PASSWORD") && !array_key_exists("lockdown-bypass", $_SESSION)) {
	$passEntry = array_key_exists("lockdown_password", $_REQUEST) ? $_REQUEST['lockdown_password'] : false;
	if (!$passEntry || $passEntry != LOCKDOWN_PASSWORD) {
		if (REQUEST_URI == "/robots.txt") {
			header("Content-Type: text/plain");
			OutputFilter::resetToNative(false);
?>User-agent: *
Disallow: /<?php
			exit;
		}
		if (REQUEST_URI != "/")
			Framework::redirect("/?nextUrl=".urlencode(REQUEST_URI));

		OutputFilter::resetToNative(false);
?><!doctype html>
<html><head><title>Private Website</title><meta name="robots" content="noindex, nofollow" /></head>
<body><h1 style="text-align: center;">Private Website</h1><p><?php
		$path = fullpath("lockdown-message.html");
		if (is_file($path))
			echo file_get_contents($path);
		else
			echo "This website is under development and access to it has been restricted by its owners.";
?></p><br /><br /><h3>Staff Login</h3>
<form style="display: table; text-align: left" action="<?php echo BASE_URI; ?>" method="POST">
<label style="font-size: 12px" for="pass">Authorization Password</label><br /><input type="password" name="lockdown_password" /><?php
		if (array_key_exists("nextUrl", $_REQUEST))
			echo "<input type='hidden' value='".htmlspecialchars($_REQUEST['nextUrl'])."' name='nextUrl' />";
?><div align="right"><input type="submit" value="Login" /></div></form></body></html><?php
		exit;
	} else {
		$_SESSION['lockdown-bypass'] = true;
		if (array_key_exists("nextUrl", $_REQUEST))
			Framework::redirect($_REQUEST['nextUrl']);
	}

}
?>
