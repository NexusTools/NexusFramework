<?php
if (isset($_POST['action']) && $_POST['action'] == "apply") {
	echo "<banner class=\"success\">Changed Applied.</banner>";
	PageCategories::setDefaultLayout($_POST['layout']);
	$layout = $_POST['layout'];
} else
	$layout = PageCategories::getDefaultLayout();
?><pagebuttons><?php
ControlPanel::renderStockButton("apply");
ControlPanel::renderStockButton("discard", "ControlPanel.loadPage('Pages', 'Manage')");
?></pagebuttons>
<form action="control://Pages/Default Category">
Layout<br />
<input id="__cp_basicPage_layout0" style="position: relative; top: -35px;" name="layout" value="0" type="radio"<?php
if($layout == 0)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_basicPage_layout0">
<?php VirtualPages::renderLayoutVisual(0, 2); ?>
</label>

<input id="__cp_basicPage_layout1" style="position: relative; top: -35px;" name="layout" value="1" type="radio"<?php
if($layout == 1)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_basicPage_layout1">
<?php VirtualPages::renderLayoutVisual(1, 2); ?>
</label>

<input id="__cp_basicPage_layout2" style="position: relative; top: -35px;" name="layout" value="2" type="radio"<?php
if($layout == 2)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_basicPage_layout2">
<?php VirtualPages::renderLayoutVisual(2, 2); ?>
</label>

<input id="__cp_basicPage_layout3" style="position: relative; top: -35px;" name="layout" value="3" type="radio"<?php
if($layout == 3)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_basicPage_layout3">
<?php
VirtualPages::renderLayoutVisual(3, 2);
$defaultWidget = isset($_POST['widget']) ? $_POST['widget'] : "html";
?></label><br />
Header Widgets<br />
<?php
if ($layout >= 2) {
	echo "<button onclick=\"";
	echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {title: ";
	echo htmlspecialchars("\"Default Category [Left Column]\"");
	echo ", section: 'cathead', subtitle: 'Footer', slot: ".VirtualPages::LEFTCOLUMN;
	echo "});return false;\"><center>Left Column<br />";
	echo VirtualPages::countWidgets(0, VirtualPages::LEFTCOLUMN, 0, "cathead");
	echo " Widgets</center></button>";
}
echo "<button onclick=\"";
echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {title: ";
echo htmlspecialchars("\"Default Category [Page Area]\"");
echo ", section: 'cathead', subtitle: 'Footer'});return false;\"><center>Page Area<br />";
echo VirtualPages::countWidgets(0, VirtualPages::PAGEAREA, 0, "cathead");
echo " Widgets</center></button>";
if ($layout == 1 || $layout == 3) {
	echo "<button onclick=\"";
	echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {title: ";
	echo htmlspecialchars("\"Default Category [Right Column]\"");
	echo ", subtitle: 'Footer', section: 'cathead', slot: ".VirtualPages::RIGHTCOLUMN;
	echo "});return false;\"><center>Right Column<br />";
	echo VirtualPages::countWidgets(0, VirtualPages::RIGHTCOLUMN, 0, "cathead");
	echo " Widgets</center></button>";
}
?>
<br />Footer Widgets<br />
<?php
if ($layout >= 2) {
	echo "<button onclick=\"";
	echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {title: ";
	echo htmlspecialchars("\"Default Category [Left Column]\"");
	echo ", section: 'catfoot', subtitle: 'Footer', slot: ".VirtualPages::LEFTCOLUMN;
	echo "});return false;\"><center>Left Column<br />";
	echo VirtualPages::countWidgets(0, VirtualPages::LEFTCOLUMN, 0, "catfoot");
	echo " Widgets</center></button>";
}
echo "<button onclick=\"";
echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {title: ";
echo htmlspecialchars("\"Default Category [Page Area]\"");
echo ", section: 'catfoot', subtitle: 'Footer'});return false;\"><center>Page Area<br />";
echo VirtualPages::countWidgets(0, VirtualPages::PAGEAREA, 0, "catfoot");
echo " Widgets</center></button>";
if ($layout == 1 || $layout == 3) {
	echo "<button onclick=\"";
	echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {title: ";
	echo htmlspecialchars("\"Default Category [Right Column]\"");
	echo ", subtitle: 'Footer', section: 'catfoot', slot: ".VirtualPages::RIGHTCOLUMN;
	echo "});return false;\"><center>Right Column<br />";
	echo VirtualPages::countWidgets(0, VirtualPages::RIGHTCOLUMN, 0, "catfoot");
	echo " Widgets</center></button>";
}
?>
</form>
