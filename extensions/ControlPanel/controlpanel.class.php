<?php
class ControlPanel {

	private static $settings;
	private static $database;
	private static $categories = Array();
	private static $naventries = Array(Array(), Array(), Array());
	private static $pageTypes = Array();
	private static $visiblePages = Array();
	private static $currentSection;
	private static $currentPage;
	private static $newPage = false;
	private static $toolbarWidgets = Array();

	const BLACKLIST_NAVIGATION = 0x0;
	const BLACKLIST_EVERYTHING = 0x1;
	const WHITELIST_EVERYTHING = 0x2;

	public static function getToolbarWidgets() {
		return self::$toolbarWidgets;
	}

	public static function registerToolbarWidget($text, $menu, $page = false, $name = false) {
		array_push(self::$toolbarWidgets, array($text, $menu, $page, $name));
	}

	public static function getDatabase() {
		return self::$database ? self::$database : (self::$database = Database::getInstance());
	}

	public static function getSettings() {
		return self::$settings ? self::$settings : (self::$settings = new Settings("ControlPanel"));
	}

	public static function registerPageType($type, $script) {
		self::$pageTypes[$type] = fullpath($script);
	}

	public static function generatePageScript($type, $definition) {
		if (!isset(self::$pageTypes[$type])) {
			$out = "<banner class=\"error\">No script found to generate pages of type `$type`.</banner>";
			$out .= "<h3>Installed Page Types:</h3>";
			foreach (self::$pageTypes as $type => $script)
				$out .= "<div>$type</div>";
			return $out;
		} else
			return include(self::$pageTypes[$type]);
	}

	private static function layerForCategory($cat) {
		return self::$categories[$cat]['layer'];
	}

	public static function isPageVisible($section, $page) {
		return isset(self::$visiblePages[$section.'/'.$page]);
	}

	public static function canUserAccessPage($section, $page) {
		if (User::isSuperAdmin())
			return true;

		$condition = self::getDatabase()->selectField("access", Array("section" => $section, "page" => $page), "condition");
		if (!$condition) {
			$default = self::getSettings()->getValue("default", self::BLACKLIST_NAVIGATION);
			switch ($default) {
			case self::BLACKLIST_NAVIGATION:
				$topLayer = self::$categories[$section]['layer'];
				return isset(self::$naventries[$topLayer][$section]) && !self::isPageVisible($section, $page);

			case self::WHITELIST_EVERYTHING:
				return true;

			default:
				return false;
			}
			return false;
		} else
			return Runtime::evaluate($condition);
	}

	public static function registerPage($cat, $name, $script, $addToNav = true, $catLayer = 1, $topLayer = 1, $asPopup = false) {
		if ($cat == "ControlPanel" && !User::isSuperAdmin())
			return;

		if ($addToNav)
			self::$visiblePages[$cat."/"."$name"] = true;

		if (!isset(self::$categories[$cat])) {
			self::$categories[$cat] = Array("assoc" => Array(),
				"layer" => $topLayer);
		} else
			$topLayer = self::$categories[$cat]['layer'];

		if ($addToNav)
			if (!self::canUserAccessPage($cat, $name))
				$addToNav = false;
			else
				if (!isset(self::$naventries[$topLayer][$cat]))
					self::$naventries[$topLayer][$cat] = Array(Array(), Array(), Array());

		if (isset(self::$categories[$cat]['assoc'][$name]))
			throw new Exception("Page Already Exists in Template.\n$name in $cat.");

		self::$categories[$cat]['assoc'][$name] = fullpath($script);
		if ($addToNav)
			array_push(self::$naventries[self::layerForCategory($cat)][$cat][$catLayer], ($asPopup ? "POPUP|" : "").$name);
	}

	private static $output;

	public static function addOutputBuffer($html) {
		self::$output .= $html;
	}

	public static function prettyPrintCondition($condition) {
		if (strlen($condition)) {
			$condition = str_replace("==", "Is", $condition);
			$condition = str_replace("&&", "And", $condition);
			$condition = str_replace("||", "Or", $condition);
			echo "If $condition";
		} else
			echo "Always";
	}

	public static function getStockIcon($name, $size = 16) {
		return Framework::getReferenceURI(DIRNAME(__FILE__)."/icons/$size/$name.svg", "image/svg+xml");
	}

	public static function renderStockButton($name, $script = false, $text = false, $imgurl = false) {
		switch ($name) {
		case "save":
			if (!$imgurl)
				$imgurl = self::getStockIcon("save");
			$text = $text ? $text : "Save";
			if (!$script)
				$script = "ControlPanel.submitForm(this);";
			break;

		case "clone":
			if (!$imgurl)
				$imgurl = self::getStockIcon("document-clone");
			$text = $text ? $text : "Save Copy";
			if (!$script)
				$script = "ControlPanel.alertDialog(\"This button does not have an associated script.<br />Please report this to the control panel's developers.<br />So they can fix the problem.\", \"Missing Script\")";
			break;

		case "publish":
			if (!$imgurl)
				$imgurl = self::getStockIcon("apply");
			$text = $text ? $text : "Publish";
			if (!$script)
				$script = "ControlPanel.submitForm(this);";
			break;

		case "delete":
			if (!$imgurl)
				$imgurl = self::getStockIcon("trash");
			$text = $text ? $text : "Delete";
			if (!$script)
				$script = "ControlPanel.alertDialog(\"This button does not have an associated script.<br />Please report this to the control panel's developer.\", \"Missing Script\")";
			break;

		case "apply":
			if (!$imgurl)
				$imgurl = self::getStockIcon("apply");
			$text = $text ? $text : "Apply Changes";
			if (!$script)
				$script = "ControlPanel.submitForm(this);";
			break;

		case "save-close":
			if (!$imgurl)
				$imgurl = self::getStockIcon("apply");
			$text = $text ? $text : "Save & Close";
			if (!$script)
				$script = "ControlPanel.submitForm(this);";
			break;

		case "discard":
			if (!$imgurl)
				$imgurl = self::getStockIcon("cancel");
			$text = $text ? $text : "Discard Changes";
			if (!$script)
				$script = "ControlPanel.alertDialog(\"The developer of this control forget to set the discard page.<br />Because of this discarding is not available.\", \"Page Not Set\")";
			break;

		case "create":
			$text = $text ? $text : "Create";
			if (!$imgurl)
				$imgurl = self::getStockIcon("save");
			if (!$script)
				$script = "ControlPanel.submitForm(this);";
			break;

		case "new":
			$text = $text ? $text : "Create";
			if (!$imgurl)
				$imgurl = self::getStockIcon("add");
			if (!$script)
				$script = "ControlPanel.alertDialog(\"This button does not have an associated script.<br />Please report this to the control panel's developer.\", \"Missing Script\")";
			break;

		case "archive":
			$text = $text ? $text : "Archive";
			if (!$imgurl)
				$imgurl = self::getStockIcon("archive");
			if (!$script)
				$script = "ControlPanel.alertDialog(\"This button does not have an associated script.<br />Please report this to the control panel's developer.\", \"Missing Script\")";
			break;

		default:
			$text = $text ? $text : $name;
			if (!$script)
				$script = "ControlPanel.alertDialog(\"This is not a valid stock button,<br />as such it has no default script available,<br />and cannot function.<br /><br />Please report this to the control panel's developers.<br />So they can fix the problem.\", \"Invalid Stock Button\")";
			break;
		}
		echo "<button value=\"$name\" onclick=\"";
		echo htmlspecialchars($script);
		echo "\">";
		if ($imgurl)
			echo "<span class=\"icon\" style=\"background: url($imgurl) no-repeat; padding-left: 20px;\"></span>";
		echo $text;
		echo "</button>";
	}

	public static function renderSortPage($database, $table, $fields, $hasParenting = false, $where = false, $idField = "rowid") {
		return include(dirname(__FILE__).DIRSEP."sort-page.inc.php");
	}

	public static function renderManagePage($database, $table, $fields, $actions, $publishable = false, $stockButtons = Array(), $sortField = "name", $hasParenting = false, $idField = "rowid", $where = false) {
		require dirname(__FILE__).DIRSEP."manage-page.inc.php";
	}

	// Use -1 for $id to put in insert mode
	public static function renderEditPage($database, $table, $id, $pages, $fields, $buttons) {
		require dirname(__FILE__).DIRSEP."edit-page.inc.php";
	}

	public static function renderRecursiveSelectOptions($entries, $value, $hideId = -1, $displayField = "name", $valueField = "rowid", $padding = false) {
		$max = count($entries) - 1;
		if ($hideId > -1) {
			for ($i = 0; $i <= $max; $i++) {
				if ($entries[$i][$valueField] === $hideId) {
					array_splice($entries, $i, 1);
					$max--;
					break;
				}
			}
		}

		if ($padding !== false)
			$epad = $padding;
		else {
			echo "<option value=\"0\">&nbsp;None</option>";
			echo "<option disabled>&nbsp;&nbsp;------------</option>";
			$epad = "";
		}

		for ($i = 0; $i <= $max; $i++) {
			$entry = $entries[$i];

			if ($padding !== false)
				if ($max == $i) {
					$ipad = "└──";
					$npad = "&nbsp;&nbsp;&nbsp;";
				} else {
					$ipad = "├──";
					$npad = "│&nbsp;&nbsp;&nbsp;";
				}
			else {
				$npad = "";
				$ipad = "";
			}

			echo "<option value=\"";
			echo htmlspecialchars($entry[$valueField]);
			echo "\"";
			if ($value == $entry[$valueField])
				echo " selected";
			echo ">$epad$ipad&nbsp;";
			echo StringFormat::displayForID($entry[$displayField]);
			echo "</option>";
			self::renderRecursiveSelectOptions($entry['children'], $value, $hideId, $displayField, $valueField, "$epad$npad");
		}
	}

	public static function getPreference($key, $default) {
		$value = self::getDatabase()->selectField("preferences", Array(
			"section" => self::$currentSection,
			"page" => self::$currentPage,
			"path" => isset($_GET['path']) ? $_GET['path'] : "",
			"variable" => $key,
			"user" => User::getID()), "data");

		if (isset($_GET[$key])) {
			if ($value === false)
				self::getDatabase()->insert("preferences", Array("data" => ($value = $_GET[$key]),
					"section" => self::$currentSection,
					"page" => self::$currentPage,
					"path" => isset($_GET['path']) ? $_GET['path'] : "",
					"variable" => $key,
					"user" => User::getID()));
			else
				self::getDatabase()->update("preferences", Array("data" => ($value = $_GET[$key])), Array(
					"section" => self::$currentSection,
					"page" => self::$currentPage,
					"path" => isset($_GET['path']) ? $_GET['path'] : "",
					"variable" => $key,
					"user" => User::getID()), false);
		}

		return $value !== false ? $value : $default;
	}

	public static function renderSelectField() {
	}

	public static function renderControlPage($fields) {

	}

	public static function changePage($page) {
		self::$newPage = $page;
	}

	public static function has($cat, $page = false) {
		return isset(self::$categories[$cat]) && (!$page || isset(self::$categories[$cat]['assoc'][$page]));
	}

	public static function getActiveSection() {
		return self::$currentSection;
	}

	public static function getActivePage() {
		return self::$currentPage;
	}

	public static function run($cat, $page) {
		if (DEBUG_MODE)
			Profiler::start("ControlPanel");

		self::$currentSection = $cat;
		self::$currentPage = $page;

		if (!isset(self::$categories[$cat]))
			throw new Exception("No Such Category `$cat`");
		if (!isset(self::$categories[$cat]['assoc'][$page]))
			throw new Exception("No Registered Page `$page`");
		if (!self::canUserAccessPage($cat, $page))
			throw new Exception("Access Denied");

		$script = self::$categories[$cat]['assoc'][$page];
		if (endsWith($script, ".json")) {
			$script = new ControlPanelPageDefinition($script);
			self::$categories[$cat]['assoc'][$page] = $script;
		}

		if ($script instanceof ControlPanelPageDefinition)
			$script = $script->getStoragePath();

		$panelScript = new PHPInclude($script);

		$breadCrumb = $panelScript->run(PHPInclude::CAPTURE_OUTPUT);
		if (self::$newPage) {
			$npage = self::$newPage;
			self::$newPage = false;
			$data = self::run(self::$currentSection, $npage);
			$oldHtml = $data['html'];
			$data['html'] = $panelScript->getOutput();
			if (is_string($oldHtml))
				$data['html'] .= $oldHtml;
			$data["uri"] = "$cat/".self::$newPage;
			if (User::isSuperAdmin() && $cat != "ControlPanel")
				$data['tools']['lock'] = Array("icon" => self::getStockIcon("locked"), "action" => "ControlPanel.loadPopup('ControlPanel', 'Edit Access Rule', {section: '".$cat."', page: '".self::$newPage."'})");
			if (!$data['html'])
				$data['html'] = "<banner class='error'>Unknown error</banner>";
			return $data;
		}

		if (is_array($breadCrumb))
			if ($breadCrumb[0] === false) {
				array_shift($breadCrumb);
				$breadCrumb = array_merge(Array($cat), $breadCrumb);
			} else
				$breadCrumb = array_merge(Array($cat, Array("title" => $page, "action" => "ControlPanel.loadPage('$cat', '$page');")), $breadCrumb);
		else
			$breadCrumb = Array($cat, Array("title" => $page, "action" => "ControlPanel.loadPage('$cat', '$page');"));

		$data = Array("breadcrumb" => $breadCrumb,
			"uri" => "$cat/$page");

		if (DEBUG_MODE) {
			$html = $panelScript->getOutput();
			Profiler::finish("ControlPanel");
			$data['html'] = "$html<span class='debug'><br /><br /><center><span style=\"font-size: 10px\">Generated in "
				.Profiler::getElapsed("ControlPanel")."ms<br />".
				Database::countQueries()." SQL Queries<br /><br />".
				"<pre style=\"text-align: left\"><b>Profiler</b>\n".
				print_r(Profiler::getTimers(), true)."</pre></span></center></span>";
		} else
			$data['html'] = $panelScript->getOutput();

		if (!$data['html'])
			$data['html'] = "<banner class='error'>Unknown error</banner>";

		$data['tools'] = Array();
		if (User::isSuperAdmin() && $cat != "ControlPanel")
			$data['tools']['lock'] = Array("icon" => self::getStockIcon("locked"), "action" => "ControlPanel.loadPopup('ControlPanel', 'Edit Access Rule', {section: '".$cat."', page: '".$page."'})");
		return $data;
	}

	public static function dumpNavBar($cat = false, $page = false) {
		$catDropHR = false;

		foreach (self::$naventries as $layer) {
			if (count($layer) < 1)
				continue;

			if ($catDropHR)
				echo "<hr />";
			else
				$catDropHR = true;

			foreach ($layer as $name => $entryLayers) {
				if (count($entryLayers) < 1)
					continue;

				echo "<item";
				if ($isActive = $name == $cat)
					echo " class=\"active\"";
				echo ">$name<submenu>";

				$dropHR = false;
				foreach ($entryLayers as $entries) {
					if (count($entries) < 1)
						continue;

					if (!$dropHR)
						$dropHR = true;
					else
						echo "<hr />";
					foreach ($entries as $entry) {
						echo "<item";

						if (startsWith($entry, "POPUP|")) {
							$entry = substr($entry, 6);
							$popup = true;
						} else
							$popup = false;

						if ($isActive && $entry == $page)
							echo " class=\"active\"";
						if ($popup)
							echo " popup";
						echo ">$entry</item>";
					}
				}

				echo "</submenu></item>";
			}
		}
	}
}

// Page Types
ControlPanel::registerPageType("sort-table", "page-types/sort-table.inc.php");
ControlPanel::registerPageType("manage-table", "page-types/manage-table.inc.php");
ControlPanel::registerPageType("edit-entry", "page-types/edit-entry.inc.php");
ControlPanel::registerPageType("create-entry", "page-types/create-entry.inc.php");

// Top Categories
ControlPanel::registerPage("Website", "Theme", "edit/theme-selector.inc.php", true, 0, 0, true);
ControlPanel::registerPage("Website", "Configure", "edit/website-configure.inc.php", true, 0, 0);
ControlPanel::registerPage("Website", "Advanced Options", "edit/advanced-options.inc.php", false);
ControlPanel::registerPage("Website", "Advanced Meta Tags", "edit/advanced-meta-tags.inc.php", false);
if (User::isSuperAdmin())
	ControlPanel::registerPage("Website", "Change Root Password", "edit/change-root-password.inc.php", false);
// Advanced
ControlPanel::registerPage("Website", "Log Viewer", "edit/log-viewer.inc.php", true, 2, 0, true);

ControlPanel::registerPage("ControlPanel", "Access Rules", "edit/controlpanel-access-rules.json", true, 0, 0);
ControlPanel::registerPage("ControlPanel", "Settings", "edit/controlpanel-configure.inc.php", true, 0, 0, true);
ControlPanel::registerPage("ControlPanel", "Edit Access Rule", "edit/controlpanel-access-control.inc.php", false);
ControlPanel::registerPage("ControlPanel", "Create Rule", "edit/controlpanel-create-rule.json", false);

ControlPanel::registerPage("Extensions", "Create", "edit/create-extension.inc.php", true, 0, 0);
ControlPanel::registerPage("Extensions", "Manage", "edit/manage-extensions.inc.php", true, 0, 0);
ControlPanel::registerPage("Extensions", "Repositories", "edit/extensions-repo.inc.php", true, 2, 0);
ControlPanel::registerPage("Extensions", "Edit", "edit/edit-extension.inc.php", false);

ControlPanel::registerPage("Framework", "Browse Cache", "edit/browse-cache.inc.php", true, 0, 2);
ControlPanel::registerPage("Framework", "Information", "edit/about-framework.inc.php", true, 0, 2, true);
ControlPanel::registerPage("Framework", "Debugger", "edit/framework-state.inc.php", true, 0, 2, true);
ControlPanel::registerPage("Framework", "Clear Cache", "edit/clear-cache.inc.php", false);
?>
