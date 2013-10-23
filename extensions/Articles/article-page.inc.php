<?php
if (!function_exists("createArticlePath")) {
	function createArticlePath($path, $page) {
		$newPage = $page;
		$resPath = explode("/", $path);
		$resPath = $resPath[count($resPath) - 1].'-';
		if (startsWith($newPage, $resPath)) {
			$newPage = substr($newPage, strlen($resPath));
			if (strlen($newPage) < 3)
				$newPage = $page;
		}

		return $path."/".$newPage;
	}
}

switch (VirtualPages::getMode()) {
case VirtualPages::RENDER_CREATOR:
?>Show in Website Navigation<br /><input type="radio" value="1" name="navbar" id="__cp__inNavbarYes" /><label for="__cp__inNavbarYes">Yes</label> 
<input type="radio" value="0" name="navbar" id="__cp__inNavbarNo" checked /><label for="__cp__inNavbarNo">No</label><br />Show in Website Footer<br /><input type="radio" value="1" name="footer" id="__cp__inFooterYes" /><label for="__cp__inFooterYes">Yes</label> 
<input type="radio" value="0" name="footer" id="__cp__inFooterNo" checked /><label for="__cp__inFooterNo">No</label><br />
Inherit Header Widgets<br /><input type="radio" value="1" name="inherit-headers" id="__cp__inheritHeadersYes" checked /><label for="__cp__inheritHeadersYes">Yes</label> 
<input type="radio" value="0" name="inherit-headers" id="__cp__inheritHeadersNo" /><label for="__cp__inheritHeadersNo">No</label><br />
Inherit Footer Widgets<br /><input type="radio" value="1" name="inherit-footers" id="__cp__inheritFootersYes" checked /><label for="__cp__inheritFootersYes">Yes</label> 
<input type="radio" value="0" name="inherit-footers" id="__cp__inheritFootersNo" /><label for="__cp__inheritFootersNo">No</label><br />Show in Website Header<br />Category<br />
<?php
	$category = isset($_POST['category']) ? $_POST['category'] : 0;
	echo "<select style=\"width: 350px; font-family: monospace, courier new\" name=\"category\" value=\"$category\">";
	ControlPanel::renderRecursiveSelectOptions(PageCategories::getCategories(), $category);
	echo "</select>";
?><br />
Tags<br />
<textarea name="tags" title="Comma Separated Tags" style="width: 350px; height: 50px;" resize="no"></textarea><br />
Snippet <help title="a.k.a. Description, used in Article Links.">?</help><br />
<textarea name="description" code="html" style="width: 350px; height: 200px; " resize="no"></textarea><br />
Content<br />
<textarea name="content" code="html" style="width: 100%; height: 500px; " resize="no"></textarea><?php
	break;

case VirtualPages::CREATE:
	$pageid = VirtualPages::getArguments();
	$db = Articles::getDatabase();
	$db->insert("instances", Array("page" => $pageid,
		"category" => $_POST['category'],
		"content" => $_POST['content'],
		"navbar" => $_POST['navbar'],
		"description" => $_POST['description'],
		"infooter" => $_POST['footer'],
		"inherit-headers" => $_POST['inherit-headers'],
		"inherit-footers" => $_POST['inherit-footers']));

	foreach (preg_split('/\s*,\s*/', $_POST['tags'], 0, PREG_SPLIT_NO_EMPTY) as $tag) {
		$db->insert("page-tags",
			Array("page" => $pageid,
			"tag" => $tag));
	}

	$path = "";
	$category = $_POST['category'];

	Articles::updateNavigation();
	return createArticlePath(PageCategories::getPathForCategory($category), StringFormat::idForDisplay($_POST['title']));

case VirtualPages::UPDATE_CONFIG:
	$pageid = VirtualPages::getArguments();
	$db = Articles::getDatabase();
	$db->update("instances", Array("category" => $_POST['category'],
		"content" => $_POST['content'],
		"navbar" => $_POST['navbar'],
		"description" => $_POST['description'],
		"infooter" => $_POST['footer'],
		"inherit-headers" => $_POST['inherit-headers'],
		"inherit-footers" => $_POST['inherit-footers']), Array("page" => $pageid));

	$db->delete("page-tags", Array("page" => $pageid));
	foreach (preg_split('/\s*,\s*/', $_POST['tags'], 0, PREG_SPLIT_NO_EMPTY) as $tag) {
		$db->insert("page-tags",
			Array("page" => $pageid,
			"tag" => $tag));
	}

	$category = $_POST['category'];
	$path = createArticlePath(PageCategories::getPathForCategory($category), StringFormat::idForDisplay($_POST['title']));
	VirtualPages::updatePath($pageid, $path);
	Articles::updateNavigation();
	break;

case VirtualPages::DELETE:
	return Articles::getDatabase()->delete("instances", Array("page" => VirtualPages::getArguments()));
	Articles::updateNavigation();
	break;

case VirtualPages::RENDER_EDITOR:
	$page = VirtualPages::getArguments();
	$data = Articles::getDatabase()->selectRow("instances", Array("page" => $page['rowid']));
	if (!$data) {
		echo "<banner class=\"error\">Missing Article Instance</banner>";
		break;
	}

	$layout = PageCategories::getLayoutForCategory($data['category']);
?>Widgets<br /><?php
	if ($layout >= 2) {
		echo "<button onclick=\"";
		echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $page[rowid], title: ";
		echo htmlspecialchars("\"`$page[title]` [Left Column]\"");
		echo ", subtitle: ";
		echo htmlspecialchars("\"$page[path]\"");
		echo ", slot: ".VirtualPages::LEFTCOLUMN;
		echo "});return false;\"><center>Left Column<br />";
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
		echo htmlspecialchars("\"`$page[title]` [Right Column]\"");
		echo ", subtitle: ";
		echo htmlspecialchars("\"$page[path]\"");
		echo ", slot: ".VirtualPages::RIGHTCOLUMN;
		echo "});return false;\"><center>Right Column<br />";
		echo VirtualPages::countWidgets($page['rowid'], VirtualPages::RIGHTCOLUMN);
		echo " Widgets</center></button>";
	}
?><br />Show in Website Navigation<br /><input type="radio" value="1" name="navbar" id="__cp__inNavbarYes"<?php if($data['navbar']) echo " checked"; ?> /><label for="__cp__inNavbarYes">Yes</label> 
<input type="radio" value="0" name="navbar" id="__cp__inNavbarNo"<?php if(!$data['navbar']) echo " checked"; ?> /><label for="__cp__inNavbarNo">No</label><br />Show in Website Footer<br /><input type="radio" value="1" name="footer" id="__cp__inFooterYes"<?php if($data['infooter']) echo " checked"; ?> /><label for="__cp__inFooterYes">Yes</label> 
<input type="radio" value="0" name="footer" id="__cp__inFooterNo"<?php if(!$data['infooter']) echo " checked"; ?> /><label for="__cp__inFooterNo">No</label><br />Inherit Header Widgets<br />
<input type="radio" value="1" name="inherit-headers" id="__cp__inheritHeaderYes"<?php if($data['inherit-headers']) echo " checked"; ?> /><label for="__cp__inheritHeaderYes">Yes</label> 
<input type="radio" value="0" name="inherit-headers" id="__cp__inheritHeaderNo"<?php if(!$data['inherit-headers']) echo " checked"; ?> /><label for="__cp__inheritHeaderNo">No</label>
<br />Inherit Footer Widgets<br /><input type="radio" value="1" name="inherit-footers" id="__cp__inheritFooterYes"<?php if($data['inherit-footers']) echo " checked"; ?> /><label for="__cp__inheritFooterYes">Yes</label> 
<input type="radio" value="0" name="inherit-footers" id="__cp__inheritFooterNo"<?php if(!$data['inherit-footers']) echo " checked"; ?> /><label for="__cp__inheritFooterNo">No</label><br />Category<br />
<?php
	echo "<select style=\"width: 350px; font-family: monospace, courier new\" name=\"category\" value=\"$data[category]\">";
	ControlPanel::renderRecursiveSelectOptions(PageCategories::getCategories(), $data['category']);
	echo "</select>";
?><br />
Tags<br />
<textarea name="tags" title="Comma Separated Tags" style="width: 350px; height: 50px;" resize="no"><?php
	foreach (Articles::getDatabase()->selectArray("page-tags", Array("page" => $page['rowid']), "tag") as $tag)
		echo htmlentities($tag).", ";
?></textarea><br />
Snippet <help title="a.k.a. Description, used in Article Links.">?</help><br />
<textarea name="description" code="html" style="width: 350px; height: 200px; " resize="no"><?php
	echo htmlspecialchars($data['description']);
?></textarea><br />
Content<br />
<textarea name="content" code="html" style="width: 100%; height: 500px; " resize="no"><?php
	echo htmlspecialchars($data['content']);
?></textarea><?php
	break;

case VirtualPages::RENDER:
	$page = VirtualPages::getArguments();
	$article = Articles::getDatabase()->selectRow("instances", Array("page" => $page['rowid']));
	$layout = Articles::getLayoutForArticle($page['rowid']);

	switch ($layout) {
	case 0: // Single Column
		echo "<column class=\"pagearea large\">";
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0);
		VirtualPages::runWidgets($page['rowid']);
		echo "<widget class='html'";
		if (User::isStaff())
			echo " edit-title='Edit Article' control-page='Pages/Edit?id=".$page['rowid']."'";
		echo ">";
		echo interpolate($article['content'], true, Triggers::broadcast("template", "page-data"));
		echo "</widget>";
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1);
		echo "</column>";
		break;

	case 1: // Right Sidebar
		echo "<column class=\"pagearea medium\">";
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0);
		VirtualPages::runWidgets($page['rowid']);
		echo "<widget edit-title='Edit Article' class='html'";
		if (User::isStaff())
			echo " control-page='Pages/Edit?id=".$page['rowid']."'";
		echo ">";
		echo interpolate($article['content'], true, Triggers::broadcast("template", "page-data"));
		echo "</widget>";
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1);
		echo "</column>";
		echo "<column class=\"sidebar right\">";
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0, VirtualPages::RIGHTCOLUMN);
		VirtualPages::runWidgets($page['rowid'], VirtualPages::RIGHTCOLUMN);
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1, VirtualPages::RIGHTCOLUMN);
		echo "</column>";
		break;

	case 2: // Left Sidebar
		echo "<column class=\"sidebar left\">";
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0, VirtualPages::LEFTCOLUMN);
		VirtualPages::runWidgets($page['rowid'], VirtualPages::LEFTCOLUMN);
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1, VirtualPages::LEFTCOLUMN);
		echo "</column><column class=\"pagearea medium\">";
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0); // Header Widgets
		VirtualPages::runWidgets($page['rowid']);
		echo "<widget edit-title='Edit Article' class='html'";
		if (User::isStaff())
			echo " control-page='Pages/Edit?id=".$page['rowid']."'";
		echo ">";
		echo interpolate($article['content'], true, Triggers::broadcast("template", "page-data"));
		echo "</widget>";
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1); // Footer Widgets
		echo "</column>";
		break;

	case 3: // Dual Sidebars
		echo "<column class=\"sidebar left\">";
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0, VirtualPages::LEFTCOLUMN);
		VirtualPages::runWidgets($page['rowid'], VirtualPages::LEFTCOLUMN);
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1, VirtualPages::LEFTCOLUMN);
		echo "</column><column class=\"pagearea small\">";
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0); // Header Widgets
		VirtualPages::runWidgets($page['rowid']);
		echo "<widget edit-title='Edit Article' class='html'";
		if (User::isStaff())
			echo " control-page='Pages/Edit?id=".$page['rowid']."'";
		echo ">";
		echo interpolate($article['content'], true, Triggers::broadcast("template", "page-data"));
		echo "</widget>";
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1); // Footer Widgets
		echo "</column><column class=\"sidebar right\">";
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0, VirtualPages::RIGHTCOLUMN);
		VirtualPages::runWidgets($page['rowid'], VirtualPages::RIGHTCOLUMN);
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1, VirtualPages::RIGHTCOLUMN);
		echo "</column>";
		break;
	}

	break;

case VirtualPages::HEADER:
	$page = VirtualPages::getArguments();
	$layout = Articles::getLayoutForArticle($page['rowid']);
	switch ($layout) {
	case 0: // Single Column
		Articles::initializeInheritedWidgets($page['rowid'], 0);
		Articles::initializeInheritedWidgets($page['rowid'], 1);
		break;

	case 1: // Right Sidebar
		Articles::initializeInheritedWidgets($page['rowid'], 0);
		Articles::initializeInheritedWidgets($page['rowid'], 1);
		Articles::initializeInheritedWidgets($page['rowid'], 0, VirtualPages::RIGHTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 1, VirtualPages::RIGHTCOLUMN);
		break;

	case 2: // Left Sidebar
		Articles::initializeInheritedWidgets($page['rowid'], 0);
		Articles::initializeInheritedWidgets($page['rowid'], 1);
		Articles::initializeInheritedWidgets($page['rowid'], 0, VirtualPages::LEFTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 1, VirtualPages::LEFTCOLUMN);
		break;

	case 3: // Dual Sidebars
		Articles::initializeInheritedWidgets($page['rowid'], 0);
		Articles::initializeInheritedWidgets($page['rowid'], 1);
		Articles::initializeInheritedWidgets($page['rowid'], 0, VirtualPages::LEFTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 1, VirtualPages::LEFTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 0, VirtualPages::RIGHTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 1, VirtualPages::RIGHTCOLUMN);
		break;
	}
	break;
}
?>
