<column class="pagearea medium"><contents><?
$productCatID = PageCategories::resolveCategoryID("Products");
if(isset($_POST['email'])) {
	$user = MailCenter::getDatabase()->selectField("user-info", Array("LOWER(`email`)" => strtolower($_POST['email'])), "rowid");
	
	
	if(User::isAdmin()){
		echo "<pre>User: $user\n";
		print_r(MailCenter::getDatabase()->selectRow("mailing-list-users", Array("user" => $user, "list" => $email['mailing-list'])));
		echo "</pre>";
	}
	if(!$user){
		?>
	<h2>Not Found</h2>
	That email is not in our database.
	<?
		return;
	}
	if(MailCenter::getDatabase()->selectField("mailing-list-users", Array("user" => $user, "list" => 1, "opt-out" => 1), "rowid")) {
		?>
	<h2>Already Opt'd Out</h2>
	This email has already been removed from our mailing list.
		<?
		return;
	}
	
	MailCenter::getDatabase()->update("mailing-list-users", Array("opt-out" => 1), Array("user" => $user, "list" => 1));
	?>
<h2>Opt'd Out</h2>
You will no longer receive updates and information from our mailing list.
	<?
	return;
}

/*

$urlid = urlencode(PageModule::getValue("url-id"));
if(isset($_POST['action']) && $_POST['action'] == "Opt Out"){
$email = PageModule::getValue("email");
if($email['mailing-list']) {
$emailAddr = $email['to'];
if(preg_match("/^[\w\s\d\-']+ <([a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+)>$/",
				$emailAddr, $matches))
	$emailAddr = $matches[1];

$user = MailCenter::getDatabase()->selectField("user-info", Array("LOWER(`email`)" => strtolower($emailAddr)), "rowid");
if(User::isAdmin()){
	echo "<pre>User: $user\n";
	print_r($email);
	print_r(MailCenter::getDatabase()->selectRow("mailing-list-users", Array("user" => $user, "list" => $email['mailing-list'])));
	echo "</pre>";
}
if(!$user || $user < 0) {
?><h2>Internal Error</h2><?
	return;
} else if(MailCenter::getDatabase()->selectField("mailing-list-users", Array("user" => $user, "list" => $email['mailing-list'], "opt-out" => 1), "rowid")) {
	?>
<h2>Already Opt'd Out</h2>
This email was sent from a mailing list you are no longer part of.
	<?
	return;
}
MailCenter::getDatabase()->update("mailing-list-users", Array("opt-out" => 1), Array("user" => $user, "list" => $email['mailing-list']));
if($email['campaign'])
	MailCenter::getDatabase()->incrementField("campaigns",
			Array("rowid" => $email['campaign']), "drop-offs");
?>
<h2>Opt'd Out</h2>
You will no longer receive updates and information from our mailing list.
<? 
} else {
?>
<h2>Oops...</h2>
You didn't receive this email through a mailing list.
<?}


} else { ?>
<h2>Opt out of our Mailing List</h2>
<form action="<? echo BASE_URL; ?>mail-center/opt-out?<? echo $urlid; ?>" method="POST">
<input type="submit" name="action" value="Opt Out" class="button" />
</form><? } */
?><center><form action="<? echo BASE_URL; ?>mail-center/opt-out" method="POST">
<h2>Opt Out of our Mailing List</h2>
<table><tr><td>
<label>Email</label><br /><input type="text" class="text" name="email" /></td></tr>
<tr><td align="right"><input value="Opt Out" class="button" type="submit"></td></tr></table></form></center></column>
<column class="sidebar right">
<? PageCategories::runCategoryWidgets($productCatID, false, VirtualPages::RIGHTCOLUMN);
PageCategories::runCategoryWidgets($productCatID, true, VirtualPages::RIGHTCOLUMN); ?></column>
