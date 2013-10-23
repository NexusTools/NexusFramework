<?php
if (isset($_POST['title'])) {
	if (($id = VirtualPages::handlePageCreation())) {
		$_POST = Array();
		$_GET = Array();
		$_GET["id"] = $id;
		$_GET['created'] = true;

		echo "<banner class=\"success\">Page created successfully.</banner>";
		ControlPanel::changePage("Edit");
		return;
	} else {
		echo "<banner class=\"error\">Something went wrong...<br /><pre>";
		print_r(VirtualPages::getDatabase()->lastError());
		echo "</pre><br />";
		var_dump($id);
		echo "</banner>";
	}
}

if (isset($_POST['type']))
	$activeType = $_POST['type'];
else
	$activeType = "basic";
?>
<pagebuttons><?php
ControlPanel::renderStockButton("save", false, "Create");
ControlPanel::renderStockButton("publish", false, "Create & Publish");
ControlPanel::renderStockButton("discard", "ControlPanel.loadPage('Pages', 'Manage')", "Discard");
$types = VirtualPages::getPageTypes();
?></pagebuttons>
<form action="control://Pages/Create">
Title<br />
<input <?php if(isset($_POST['condition'])) echo "value=\"" .htmlspecialchars($_POST['title']). "\" ";?>name="title" type="text" class="text large"><br />
Condition<br />
<input <?php if(isset($_POST['condition'])) echo "value=\"" .htmlspecialchars($_POST['condition']). "\" ";?>name="condition" style="width: 350px" type="condition" class="text"><br />
Type<br />
<select value="<?php echo $activeType; ?>" onchange="if(!this.oldType){this.oldType='<?php echo $types[0]; ?>'} $('__cp_page_' + this.oldType).hide(); this.oldType = this.value; $('__cp_page_' + this.value).show();" name="type" style="width: 350px">
<?php
foreach ($types as $type) {
	echo "<option value=\"$type\"";
	if ($activeType == $type)
		echo " selected";
	echo ">".StringFormat::displayForID($type)."</option>";
}
?>
</select>
<?php
$first = true;
foreach ($types as $type) {
	echo "<div ";
	if ($first)
		$first = false;
	else
		echo "style=\"display: none\" ";
	echo "id=\"__cp_page_$type\">";
	VirtualPages::runPage($type, VirtualPages::RENDER_CREATOR, false);
	echo "</div>";
}
?>
</form>
