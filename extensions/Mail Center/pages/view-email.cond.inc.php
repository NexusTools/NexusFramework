<?php
$id = false;
$format = false;
switch (count($_GET)) {
case 1:
	$rawID = array_keys($_GET);
	$id = base64_decode($rawID = $rawID[0]);
	break;
case 2:
	$rawID = array_keys($_GET);
	$format = $rawID[0];
	$id = base64_decode($rawID = $rawID[1]);
	break;
}
if ($id && ($email = MailCenter::getEmail($id))) {
	$basePath = MailCenter::getStoragePathForEmail($id, false);
	if ($basePath) {
		$ffile = false;
		if ($format && !is_file(($ffile = "$basePath"."payload.$format")))
			return false;

		PageModule::setValue("email", $email);
		PageModule::setValue("email-id", $id);
		PageModule::setValue("base-path", $basePath);
		PageModule::setValue("raw-id", $rawID);
		PageModule::setValue("ffile", $ffile);
		PageModule::setValue("ftype", $format == "html" ? "text/html" : "text/plain");
		return true;
	}
}
return false;
?>
