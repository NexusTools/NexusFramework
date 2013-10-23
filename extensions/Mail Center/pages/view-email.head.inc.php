<?php
OutputFilter::resetToNative(false);
$id = PageModule::getValue("email-id");
$rawID = urlencode(PageModule::getValue("raw-id"));
$ffile = PageModule::getValue("ffile");
$basePath = PageModule::getValue("base-path");

if ($ffile)
	Framework::serveFile($ffile, PageModule::getValue("ftype"));
else {
	$email = PageModule::getValue("email");
	$expires = Database::timestampToTime($email['expires']);
	Template::reset();
	Template::setRobotsPolicy(false);
	Template::setTitle($email['subject']);
	Template::writeHeader();
	echo "<table cellpadding=\"6\" cellspacing=\"0\" style=\"background-color: white; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%\"><tr><td>";
	echo "<span style=\"white-space: nowrap; color: black; font-weight: bold; font-size: 150%;\">".
		Template::getTitle()."</span>";
	echo "</td><td align=\"right\" style=\"width: 100%\">";
	echo "<a href=\"";
	echo BASE_URL;
	echo "mail-center/opt-out?$rawID";
	echo "\" style=\"font-size: 12px\">Opt Out</a><br />";
	if (is_file($basePath."payload.html")) {
		echo "<a href=\"";
		echo BASE_URL;
		echo "mail-center/view-email?html&$rawID";
		echo "\" target=\"email\">HTML Version</a><br />";
		$format = "html";
	} else
		$format = "txt";
	echo "<a href=\"";
	echo BASE_URL;
	echo "mail-center/view-email?txt&$rawID";
	echo "\" target=\"email\">Text Version</a>";
	if (DEBUG_MODE || User::isAdmin()) {
		echo "<br /><a href=\"";
		echo BASE_URL;
		echo "mail-center/view-email?raw&$rawID";
		echo "\" target=\"email\">Raw Email</a>";
	}
	echo "</td></tr>"; /*
	 echo "<tr><td style=\"padding-left: 20px; font-size: 12px; white-space: nowrap;\">To:<br />From:";
	 echo "</td><td style=\"font-size: 12px; white-space: nowrap;\">";
	 echo htmlentities($email['to']);
	 echo "<br />";
	 echo htmlentities($email['from']);
	 echo "</td><td valign=\"top\" style=\"font-size: 12px; white-space: nowrap;\">Date:";
	 if($expires)
	 echo "<br />Expires:";
	 echo "</td><td valign=\"top\" style=\"font-size: 12px; white-space: nowrap;\">";
	 echo StringFormat::formatDate(Database::timestampToTime($email['sent']));
	 if($expires)
	 echo "<br />" . StringFormat::formatDate(Database::timestampToTime($email['expires']));
	
	 echo "</td></tr>";
	 */
	echo "<tr><td style=\"border-top: solid 2pt black; height: 100%;\" colspan=\"2\"><iframe style=\"width: 100%; height: 100%; border: none; \" src=\"";
	echo BASE_URL;
	echo "mail-center/view-email?$format&$rawID";
	echo "\" border=\"0\" name=\"email\" id=\"email\">iframes must be enabled to view this content.</iframe></td></tr></table>";
?><script>
	emailFrame = document.getElementById("email");
	function setupLink(link){
		if(link.setAttribute)
			link.setAttribute("target", "_blank");
		else
			link.target = "_blank";
		link.onclick = function(){
			if(link.getAttribute)
				window.open(link.getAttribute("href"));
			else
				window.open(link.href);
			return false;
		}
	}
	function fixFrameLinks(){
		try {
			var emailDocument = emailFrame.contentWindow.document;
			for(var i in emailDocument.links)
				setupLink(emailDocument.links[i]);
		}catch(e){
			console.log(e);
		}
	}
	fixFrameLinks();
	emailFrame.onload = fixFrameLinks;</script><?php
	Template::writeFooter();
}
exit;
?>
