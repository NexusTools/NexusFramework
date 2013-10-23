<?php
class VirtualPages {

	private static $types = Array();
	private static $database = false;
	private static $account = false;
	private static $widgets = Array();
	private static $widgetTypes = Array(Array(), Array(), Array());
	private static $activeArguments;
	private static $activeMode;
	private static $pageTypes = Array();

	const CREATE = 0x0;
	const HEADER = 0x1;
	const RENDER = 0x2;
	const RENDER_EDITOR = 0x3;
	const RENDER_CREATOR = 0x4;
	const UPDATE_CONFIG = 0x5;
	const DELETE = 0x6;

	const PAGEAREA = 0x0;
	const SIDEBAR = 0x1;
	const EMBEDDABLE = 0x2;
	const EITHER = 0x3;

	const LEFTCOLUMN = 0x1;
	const RIGHTCOLUMN = 0x2;

	public static function initialize() {
		self::$database = Database::getInstance();
	}

	public static function handlePageCreation() {
		$id = self::$database->insert("pages", Array(
			"title" => $_POST['title'],
			"condition" => $_POST['condition'],
			"type" => $_POST['type'],
			"published" => ($_POST['action'] == "publish")));

		if ($id) {

			$path = self::runPage($_POST['type'], self::CREATE, $id);
			if (!$path) {
				self::dropPage($id);
				return false;
			}

			self::updatePath($id, $path);
			return $id;
		} else {
			throw self::$database->lastException();
			return false;
		}
	}

	public static function getLayoutVisual($id, $scale = 1) {
		$html = "<img src=\"";
		$html .= Framework::getReferenceURI(dirname(__FILE__).DIRSEP."images".DIRSEP."layout$id[layout].png");
		$html .= "\" width=\"";
		$html .= 42.5 * $scale;
		$html .= "\" height=\"";
		$html .= 26 * $scale;
		$html .= "\" />";
		return $html;
	}

	public static function renderLayoutVisual($id, $scale = 1) {
		echo "<img src=\"";
		echo Framework::getReferenceURI(dirname(__FILE__).DIRSEP."images".DIRSEP."layout$id.png");
		echo "\" width=\"";
		echo 42.5 * $scale;
		echo "\" height=\"";
		echo 26 * $scale;
		echo "\" />";
	}

	public static function updatePath($id, $path) {
		if (!startsWith($path, "/"))
			$path = "/$path";
		return self::$database->update("pages", Array("path" => strtolower(str_replace("//", "/", $path))), $id);
	}

	public static function getDatabase() {
		return self::$database;
	}

	public static function fetch($id) {
		return self::$database->selectRow("pages", Array("rowid" => $id));
	}

	public static function fetchPage($path, $verify = false) {
		$where = Array();
		if ($verify)
			$where['published'] = true;

		if ($ignoreCondition = is_numeric($path))
			$where['rowid'] = $path;
		else
			$where['path'] = $path;

		foreach (self::$database->select("pages", $where) as $page) {
			if ($ignoreCondition || (!$page['condition'] || eval("return $page[condition];")))
				return $page;
		}
		return false;
	}

	public static function getMode() {
		return self::$activeMode;
	}

	public static function getArguments() {
		return self::$activeArguments;
	}

	public static function togglePublished($id) {
		return self::$database->update("pages", Array("published" => ":NOT published"), $id);
	}

	public static function dropPage($id) {
		$page = self::fetchPage($id);
		VirtualPages::runPage($page['type'], VirtualPages::DELETE, $id);

		self::$database->delete("pages", Array("rowid" => $id));
		foreach (self::$database->select("widgets", Array("location" => $id, "section" => "pages")) as $child)
			self::dropWidget($child['rowid']);
	}

	public static function processPageUpdate() {
		$page = self::fetchPage($_GET['id']);
		if (!$page)
			return;

		self::$database->update("pages", Array("title" => $_POST['title'], "condition" => $_POST['condition']), $_GET['id']);
		self::runPage($page['type'], VirtualPages::UPDATE_CONFIG, $page['rowid']);
	}

	public static function runPage($type, $mode, $args = false, $echoOutput = false) {
		if (!isset(self::$pageTypes[$type])) {
			echo "MISSING PAGE SCRIPT FOR `$type`";
			return;
		}

		$handler = self::$pageTypes[$type];
		self::$activeArguments = $args;
		self::$activeMode = $mode;
		if ($echoOutput) {
			$path = getcwd();
			@chdir($handler['path']);
			$script = new PHPInclude($handler['script']);
			$script->run(PHPInclude::CAPTURE_OUTPUT);
			@chdir($path);
			return $script->getOutput();
		} else
			return require_chdir($handler['script'], $handler['path']);

	}

	public static function createWidget($type, $location, $slot = self::PAGEAREA, $parent = 0, $section = "pages", $config = false) {
		return self::$database->insert("widgets", Array(
			"type" => $type,
			"location" => $location,
			"section" => $section,
			"config" => serialize($config ? $config : self::runWidget($type, self::CREATE)),
			"parent" => $parent,
			"slot" => $slot
		));
	}

	public static function fetchWidget($id) {
		$widget = self::$database->selectRow("widgets", Array("rowid" => $id));
		if ($widget)
			$widget['config'] = unserialize($widget['config']);
		return $widget;
	}

	public static function runWidgetHeaders($location, $section = "pages") {
		$widgets = self::$database->select("widgets", Array(
			"location" => $location,
			"section" => $section
		));
		foreach ($widgets as $widget)
			self::runWidget($widget['type'], VirtualPages::HEADER, unserialize($widget['config']));
	}

	public static function runWidgets($location, $slot = self::PAGEAREA, $section = "pages", $customClass = false) {
		foreach (self::fetchWidgets($location, $slot, 0, $section) as $widget)
			self::runWidget($widget['type'], self::RENDER, unserialize($widget['config']), $widget['rowid'], $customClass);
	}

	public static function fetchWidgets($location, $slot = self::PAGEAREA, $parent = 0, $section = "pages") {
		return self::$database->select("widgets", Array(
			"location" => $location,
			"section" => $section,
			"slot" => $slot,
			"parent" => $parent
		));
	}

	public static function countWidgets($location, $slot = self::PAGEAREA, $parent = 0, $section = "pages") {
		return self::$database->countRows("widgets", Array(
			"location" => $location,
			"section" => $section,
			"slot" => $slot,
			"parent" => $parent
		));
	}

	public static function updateWidget($id, $config) {
		return self::$database->update("widgets", Array(
			"config" => serialize($config)
		), $id);
	}

	public static function dropWidget($id) {
		$widget = self::fetchWidget($id);
		if ($widget) {
			foreach (self::$database->select("widgets", Array("parent" => $id)) as $child)
				self::dropWidget($child['rowid']);
			return self::$database->delete("widgets", Array("rowid" => $id));
		} else
			return false;
	}

	public static function runWidget($type, $mode, $args = false, $widgetID = false, $customClass = false) {
		try {
			if (!isset(self::$widgets[$type]))
				throw new Exception("No provider registered for Widget");

			$handler = self::$widgets[$type];
			if ($args)
				self::$activeArguments = $args;
			self::$activeMode = $mode;

			if ($mode == self::RENDER) {
				if (array_key_exists("title", $args))
					$customName = trim($args['title']);
				else
					if (array_key_exists("__widget_name", $args))
						$customName = trim($args['__widget_name']);
					else
						$customName = false;

				echo "<widget";
				if ($widgetID && User::isStaff()) {
					echo " vpages-widget='$widgetID' vpages-name='".htmlspecialchars($customName ? $customName : StringFormat::displayForId($type))."'";
				}
				$customClass = $customClass ? " ".StringFormat::idForDisplay($customClass) : "";
				$customName = $customName ? " ".StringFormat::idForDisplay("Title $customName") : "";
				echo " class='$type$customName$customClass'>";
			}
			$ret = require_chdir($handler['script'], $handler['path']);
			if ($mode == self::RENDER)
				echo "</widget>";
			return $ret;
		} catch (Exception $e) {
			if ($mode == self::RENDER)
				echo "<div>EXCEPTION OCCURED WHILE RUNNING WIDGET `$type`\n<br />".$e->getMessage()."</div>";
			else
				if ($mode == self::RENDER_EDITOR)
					echo "<banner class=\"error\">EXCEPTION OCCURED WHILE RUNNING WIDGET `$type`\n<br />".$e->getMessage()."</banner>";
		}
	}

	public static function queryPages($where = false, $start = 0, $limit = 10) {
		return self::$database->queryRows("pages", $where, $start, $limit);
	}

	public static function registerWidget($name, $script, $slot = self::PAGEAREA, $embeddable = false) {
		self::$widgets[$name] = Array("script" => fullpath($script),
			"path" => getcwd());

		if ($slot == self::PAGEAREA || $slot == self::EITHER)
			array_push(self::$widgetTypes[self::PAGEAREA], $name);

		if ($slot == self::SIDEBAR || $slot == self::EITHER)
			array_push(self::$widgetTypes[self::SIDEBAR], $name);

		if ($embeddable || $slot == self::EMBEDDABLE)
			array_push(self::$widgetTypes[self::EMBEDDABLE], $name);
	}

	public static function registerPageType($name, $script) {
		self::$pageTypes[$name] = Array("script" => fullpath($script),
			"path" => getcwd());
	}

	public static function getPageTypes() {
		return array_keys(self::$pageTypes);
	}

	public static function getWidgetTypes($slot = self::PAGEAREA) {
		return self::$widgetTypes[$slot];
	}

}

VirtualPages::initialize();
VirtualPages::registerPageType("basic", "basic-page.inc.php");
VirtualPages::registerWidget("html", "widgets/html.inc.php", VirtualPages::EITHER, true);
VirtualPages::registerWidget("spacer", "widgets/spacer.inc.php", VirtualPages::EITHER, true);
//VirtualPages::registerWidget("column-box", "widgets/expanded-column-box.inc.php", VirtualPages::PAGEAREA);
VirtualPages::registerWidget("style", "widgets/style.inc.php", VirtualPages::EITHER, true);
?>
