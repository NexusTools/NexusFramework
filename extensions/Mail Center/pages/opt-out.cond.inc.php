<?php
return true;

$id = false;
$format = false;
if (count($_GET) == 1) {
	$rawID = array_keys($_GET);
	$id = base64_decode($rawID = $rawID[0]);
} else
	return false;
if ($id && ($email = MailCenter::getEmail($id))) {
	$basePath = MailCenter::getStoragePathForEmail($id, false);
	if ($basePath) {
		$ffile = false;
		if ($format && !is_file(($ffile = "$basePath"."payload.$format")))
			return false;

		PageModule::setValue("email", $email);
		PageModule::setValue("email-id", $id);
		PageModule::setValue("base-path", $basePath);
		PageModule::setValue("url-id", $rawID);
		return true;
	}
}
return false;
?>
