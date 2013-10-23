<?php
$email = MailCenter::getEmail($_GET['id']);

if ($mail['expires'] < time() && $mail['expires'] > 0)
	echo "<banner>This message has expired, it is no longer being tracked.</banner><br />";

$basePath = MailCenter::getStoragePathForEmail($_GET['id'], false);
$rawID = urlencode(base64_encode($_GET['id']));
echo "<pagebuttons>";
if (is_file($basePath."payload.html")) {
	echo "<a href=\"";
	echo BASE_URL;
	echo "mail-center/view-email?html&$rawID";
	echo "\" target=\"__cp_emailPreview\">HTML Version</a> - ";
	$format = "html";
} else
	$format = "txt";
echo "<a href=\"";
echo BASE_URL;
echo "mail-center/view-email?txt&$rawID";
echo "\" target=\"__cp_emailPreview\">Text Version</a>";
echo " - <a href=\"";
echo BASE_URL;
echo "mail-center/view-email?raw&$rawID";
echo "\" target=\"__cp_emailPreview\">Raw Email</a>";
echo "</pagebuttons>";

echo "<table style=\"width: 100%; height: 100%\"><tr><td><h1 style=\"margin-bottom: 0px;\">$email[subject] <sup>$email[views] Views, $email[interactions] Interactions</sup></h1>";
echo "<sup>To: ";
echo htmlentities($email['to']);
echo "<br />From: ";
echo htmlentities($email['from']);
echo "";
echo "</td></tr><tr><td colspan=\"2\" style=\"height: 100%; border: solid 1pt black;\"><iframe name=\"__cp_emailPreview\" id=\"__cp_emailPreview\" style=\"border:none; width: 100%; height: 100%;\" border=\"0\" src=\"";
echo BASE_URL;
echo "mail-center/view-email?$format&$rawID\"></iframe></td></tr></table>";

return Array(false, Array("title" => "Outbox", "action" => "ControlPanel.loadPage('Mail Center', 'Outbox');"), "Message `".htmlentities(base64_encode($_GET['id']))."`");
?>
