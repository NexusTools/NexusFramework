<?php
switch (VirtualPages::getMode()) {
case VirtualPages::CREATE:
	$pageid = VirtualPages::getArguments();
	if (!VirtualPages::createWidget($_POST['widget'], $pageid))
		return false;

	if (VirtualPages::getDatabase()->insert("basic-pages",
		Array("id" => $pageid,
		"layout" => $_POST['layout'])))
		return $_POST['path'];

	return false;
	break;

case VirtualPages::DELETE:
	return VirtualPages::getDatabase()->delete("basic-pages", Array("id" => VirtualPages::getArguments()));
	break;

case VirtualPages::UPDATE_CONFIG:
	VirtualPages::updatePath(VirtualPages::getArguments(), $_POST['path']);
	break;

case VirtualPages::RENDER:
	$page = VirtualPages::getArguments();
	$layout = VirtualPages::getDatabase()->selectField("basic-pages", Array("id" => $page['rowid']), "layout");
	switch ($layout) {
	case 0: // Single column
		echo "<column class=\"pagearea large\">";
		VirtualPages::runWidgets($page['rowid']);
		echo "</column>";
		break;

	case 1: // Right Sidebar
		echo "<column class=\"pagearea medium\">";
		VirtualPages::runWidgets($page['rowid'], VirtualPages::PAGEAREA);
		echo "</column><column class=\"sidebar right\">";
		VirtualPages::runWidgets($page['rowid'], VirtualPages::RIGHTCOLUMN);
		echo "</column>";
		break;

	case 2: // Left Sidebar
		echo "<column class=\"sidebar left\">";
		VirtualPages::runWidgets($page['rowid'], VirtualPages::LEFTCOLUMN);
		echo "</column><column class=\"pagearea medium\">";
		VirtualPages::runWidgets($page['rowid']);
		echo "</column>";
		break;

	case 3: // Dual Sidebars
		echo "<column class=\"sidebar left\">";
		VirtualPages::runWidgets($page['rowid'], VirtualPages::LEFTCOLUMN);
		echo "</column><column class=\"pagearea small\">";
		VirtualPages::runWidgets($page['rowid']);
		echo "</column><column class=\"sidebar right\">";
		VirtualPages::runWidgets($page['rowid'], VirtualPages::RIGHTCOLUMN);
		echo "</column>";
		break;
	}
	break;

case VirtualPages::RENDER_EDITOR:
	$page = VirtualPages::getArguments();
?>Path<br />
<input value="<?php echo htmlspecialchars($page['path']); ?>" name="path" style="width: 350px" type="text" class="text"><br />
Layout<br /><?php
	$layout = VirtualPages::getDatabase()->selectField("basic-pages", Array("id" => $page['rowid']), "layout");
	VirtualPages::renderLayoutVisual($layout, 2);
?><br />Widgets<br /><?php
	if ($layout >= 2) {
		echo "<button onclick=\"";
		echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $page[rowid], title: ";
		echo htmlspecialchars("\"`$page[title]` [Left column]\"");
		echo ", subtitle: ";
		echo htmlspecialchars("\"$page[path]\"");
		echo ", slot: ".VirtualPages::LEFTCOLUMN;
		echo "});return false;\"><center>Left column<br />";
		echo VirtualPages::countWidgets($page['rowid'], VirtualPages::LEFTCOLUMN);
		echo " Widgets</center></button>";
	}
	echo "<button onclick=\"";
	echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $page[rowid], title: ";
	echo htmlspecialchars("\"`$page[title]` [Page Area]\"");
	echo ", subtitle: ";
	echo htmlspecialchars("\"$page[path]\"");
	echo "});return false;\"><center>Page Area<br />";
	echo VirtualPages::countWidgets($page['rowid']);
	echo " Widgets</center></button>";
	if ($layout == 1 || $layout == 3) {
		echo "<button onclick=\"";
		echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $page[rowid], title: ";
		echo htmlspecialchars("\"`$page[title]` [Right column]\"");
		echo ", subtitle: ";
		echo htmlspecialchars("\"$page[path]\"");
		echo ", slot: ".VirtualPages::RIGHTCOLUMN;
		echo "});return false;\"><center>Right column<br />";
		echo VirtualPages::countWidgets($page['rowid'], VirtualPages::RIGHTCOLUMN);
		echo " Widgets</center></button>";
	}
	break;

case VirtualPages::RENDER_CREATOR:
?>Path<br />
<input <?php if(isset($_POST['path'])) echo "value=\"" .htmlspecialchars($_POST['path']). "\" "; ?>name="path" style="width: 350px" type="text" class="text"><br />
Layout<br />
<input id="__cp_basicPage_layout0" style="position: relative; top: -35px;" name="layout" value="0" type="radio"<?php
if(!isset($_POST['layout']) || $_POST['layout'] == 0)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_basicPage_layout0">
<?php VirtualPages::renderLayoutVisual(0, 2); ?>
</label>

<input id="__cp_basicPage_layout1" style="position: relative; top: -35px;" name="layout" value="1" type="radio"<?php
if(isset($_POST['layout']) && $_POST['layout'] == 1)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_basicPage_layout1">
<?php VirtualPages::renderLayoutVisual(1, 2); ?>
</label>

<input id="__cp_basicPage_layout2" style="position: relative; top: -35px;" name="layout" value="2" type="radio"<?php
if(isset($_POST['layout']) && $_POST['layout'] == 2)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_basicPage_layout2">
<?php VirtualPages::renderLayoutVisual(2, 2); ?>
</label>

<input id="__cp_basicPage_layout3" style="position: relative; top: -35px;" name="layout" value="3" type="radio"<?php
if(isset($_POST['layout']) && $_POST['layout'] == 3)
	echo " checked";
?> />
<label style="cursor: pointer" for="__cp_basicPage_layout3">
<?php
	VirtualPages::renderLayoutVisual(3, 2);
	$defaultWidget = isset($_POST['widget']) ? $_POST['widget'] : "html";
?><br />
</label>Default Page Widget<br />
<select value="<?php echo htmlspecialchars($defaultWidget); ?>" name="widget" style="width: 350px">
<?php
	foreach (VirtualPages::getWidgetTypes() as $type) {
		echo "<option value=\"$type\"";
		if ($defaultWidget == $type)
			echo " selected";
		echo ">".StringFormat::displayForID($type)."</option>";
	}
?>
</select><?php
	break;
}
?>
