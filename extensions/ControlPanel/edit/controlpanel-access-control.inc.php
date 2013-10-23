<?php
$value = "";
if (!$_GET['id']) {
	if (!$_GET['section'] || !$_GET['page'])
		throw new Exception("Missing Identifier");
	$section = $_GET['section'];
	$page = $_GET['page'];
	$itemId = ControlPanel::getDatabase()->selectField("access", Array("section" => $section, "page" => $page), "rowid");
	if ($itemId)
		$value = ControlPanel::getDatabase()->selectField("access", Array("rowid" => $itemId), "condition");
} else {
	$data = ControlPanel::getDatabase()->selectRow("access", Array("rowid" => $_GET['id']));
	if (!$data)
		throw new Exception("Invalid Rule ID Specified");

	$value = $data['condition'];
	$section = $data['section'];
	$page = $data['page'];
}

$error = false;
if (isset($_POST['condition'])) {
	$condition = trim($_POST['condition']);
	if (!$itemId && !strlen($condition))
		$error = "Required";
	else {
		$error = "An internal error occured;";
		if ($itemId) {
			if (!strlen($condition)) {
				if (ControlPanel::getDatabase()->delete("access", Array("rowid" => $itemId))) {
					echo "Rule Deleted";
					return;
				}
			} else
				if (ControlPanel::getDatabase()->update("access", Array("condition" => $condition), Array("rowid" => $itemId))) {
					echo "Rule Updated";
					return;
				}
		} else
			if (ControlPanel::getDatabase()->insert("access", Array("section" => $section, "page" => $page, "condition" => $condition))) {
				echo "Rule Created";
				return;
			} else
				$error = "An internal error occured;";

	}
}
?><pagebuttons><?php
ControlPanel::renderStockButton("apply");
ControlPanel::renderStockButton("discard", "ControlPanel::loadPage('ControlPanel', 'Access Rules')");
?></pagebuttons>
<h3>For page `<?php echo $page; ?>` of section `<?php echo $section; ?>`</h3>
<h4>This page is <?php echo ControlPanel::isPageVisible($section, $page) ? "in the Navigation Bar" : "a Subpage"; ?></h4>
<form method="POST" action="control://ControlPanel/Edit Access Rule?<?php
if($itemId)
    echo "id=" . $itemId;
else
    echo "page=" . urlencode($page) . "&section=" . urlencode($section);
?>"><?php
if ($error)
	echo '<span style="color:red; font-size: 10px;">'.htmlentities($error).'</span><br />';
?><input class="text large" name="condition" value="<?php echo htmlentities($value); ?>"></form>
