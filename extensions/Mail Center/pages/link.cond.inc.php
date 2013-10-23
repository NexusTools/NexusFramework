<?php
if (count($_GET) === 1) {
	$id = array_keys($_GET);
	MailCenter::followLink(base64_decode($id[0]));
}
?>
