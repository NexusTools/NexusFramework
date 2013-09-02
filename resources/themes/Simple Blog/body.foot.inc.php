<left><?
$footerLinks = Triggers::broadcast("Template", "GetFooterLinks");
foreach($footerLinks as $text => $entry) {
	echo "<a href='$entry'>$text</a>";
}
?></left><right>Theme by Katelyn - Get Your Own</right>
