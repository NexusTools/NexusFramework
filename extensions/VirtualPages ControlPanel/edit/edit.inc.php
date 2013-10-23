<?php
if (isset($_POST['action']) && startsWith($_POST['action'], "save")) {
	VirtualPages::processPageUpdate();
	echo "<banner class=\"success\">All changes saved.</banner>";
	if ($_POST['action'] == "save-close") {
		ControlPanel::changePage("Manage");
		return;
	}

}
$page = VirtualPages::fetchPage($_GET['id']);
?><pagebuttons><?php
ControlPanel::renderStockButton("save");
ControlPanel::renderStockButton("save-close");
ControlPanel::renderStockButton("clone");
ControlPanel::renderStockButton("delete", "ControlPanel.loadPopup('Pages', 'Delete', {id: $page[rowid]})");
ControlPanel::renderStockButton("discard", "ControlPanel.loadPage('Pages', 'Manage')");
?></pagebuttons>
<form action="control://Pages/Edit?id=<?php echo $_GET['id']; ?>">
Title<br />
<input name="title" type="text" class="text large" value="<?php echo $page['title']; ?>"><br />
Condition<br />
<input name="condition" style="width: 350px" type="condition" class="text" value="<?php echo htmlentities($page['condition']); ?>"><br />
Type<br />
<input style="width: 350px" type="text" class="text" value="<?php echo htmlentities(StringFormat::displayForID($page['type'])); ?>" readonly><br />
<?php
VirtualPages::runPage($page['type'], VirtualPages::RENDER_EDITOR, $page);
?>
</form>
