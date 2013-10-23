<?php
switch ($mode) {
case EditCore::RENDER:
	$catid = $_GET['id'];
	$section = array_key_exists("footer", $meta) ? "catfoot" : "cathead";
	$subtitle = array_key_exists("footer", $meta) ? "Footer" : "Header";
	$category = PageCategories::getDatabase()->selectRow("categories", Array("rowid" => $catid));
	$layout = PageCategories::getLayoutForCategory($catid);
	if ($layout >= 2) {
		echo "<button onclick=\"";
		echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $catid, title: ";
		echo htmlspecialchars("\"Category `$category[name]` [Left Column]\"");
		echo ", section: '$section', subtitle: '$subtitle', slot: ".VirtualPages::LEFTCOLUMN;
		echo "});return false;\"><center>Left Column<br />";
		echo VirtualPages::countWidgets($catid, VirtualPages::LEFTCOLUMN, 0, $section);
		echo " Widgets</center></button>";
	}
	echo "<button onclick=\"";
	echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $catid, title: ";
	echo htmlspecialchars("\"Category `$category[name]` [Page Area]\"");
	echo ", section: '$section', subtitle: '$subtitle'});return false;\"><center>Page Area<br />";
	echo VirtualPages::countWidgets($catid, VirtualPages::PAGEAREA, 0, $section);
	echo " Widgets</center></button>";
	if ($layout == 1 || $layout == 3) {
		echo "<button onclick=\"";
		echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $catid, title: ";
		echo htmlspecialchars("\"Category `$category[name]` [Right Column]\"");
		echo ", subtitle: '$subtitle', section: '$section', slot: ".VirtualPages::RIGHTCOLUMN;
		echo "});return false;\"><center>Right Column<br />";
		echo VirtualPages::countWidgets($catid, VirtualPages::RIGHTCOLUMN, 0, $section);
		echo " Widgets</center></button>";
	}
	break;
}
?>
