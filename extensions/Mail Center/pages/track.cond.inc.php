<?php
$id = false;
$format = false;
if (count($_GET) == 1) {
	$rawID = array_keys($_GET);
	$id = base64_decode($rawID = $rawID[0]);
}
if ($id) {
	$basePath = MailCenter::getStoragePathForEmail($id, false);
	if ($basePath) {
		PageModule::setValue("email-id", $id);
		return true;
	}
}
return false;
?>
