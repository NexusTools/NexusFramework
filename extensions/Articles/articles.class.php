<?php
class Articles {

	private static $database;

	public static function getDatabase() {
		return self::$database;
	}

	public static function init() {
		self::$database = Database::getInstance();
	}

	private static function processChildren($parent, &$entries, $path) {
		foreach ($entries as & $entry) {
			if ($entry['genpage'])
				$entry['path'] = "$path/".StringFormat::idForDisplay($entry['name']);
			else
				$entry['path'] = null;
			self::processChildren($entry['rowid'], $entry['children'], $entry['path']);
		}

		foreach (self::queryArticlesByCategory($parent, false, true) as $article)
			array_push($entries, $article);

	}

	public static function updateNavigation() {
		register_shutdown_function(Array(__CLASS__, "updateNavigationNow"));
	}

	public static function updateNavigationNow() {
		Navigation::invalidateMenu("Articles");
		foreach (PageCategories::getDatabase()->selectRecursive("categories", 0, false,
			Array("navbar" => 1, "published" => 1)) as $category) {

			if ($category['genpage'])
				$category['path'] = "/".StringFormat::idForDisplay($category['name']);
			else
				$category['path'] = null;
			self::processChildren($category['rowid'], $category['children'], $category['path']);
			Navigation::registerMenu($category, "Articles");
		}

		foreach (self::queryArticlesByCategory(0, false, true) as $article)
			Navigation::registerMenu($article, "Articles");
	}

	public static function fetchArticle($pageid) {
		return self::$database->selectRow("instances", Array("page" => $pageid));
	}

	public static function queryArticlesByCategory($catid, $checkCondition = false, $onlyNavbar = false) {
		$articles = Array();
		$catid = PageCategories::resolveCategoryID($catid);
		if ($onlyNavbar)
			$rawArticles = self::$database->select("instances", Array("category" => $catid, "navbar" => 1));
		else
			$rawArticles = self::$database->select("instances", Array("category" => $catid));

		foreach ($rawArticles as $article) {
			$page = VirtualPages::fetch($article['page']);
			if ($page && $page['published'] && (!$checkCondition || Framework::testCondition($page['condition'])))
				array_push($articles, array_merge($article, $page));
		}

		return $articles;
	}

	public static function queryFooterArticles($checkCondition = true) {
		$articles = Array();
		foreach (self::$database->select("instances", Array("infooter" => true)) as $article) {
			$page = VirtualPages::fetch($article['page']);
			if ($page && $page['published'] && (!$checkCondition || Framework::testCondition($page['condition'])))
				array_push($articles, array_merge($article, $page));
		}

		return $articles;
	}

	public static function getLayoutForArticle($id) {
		$article = self::fetchArticle($id);
		return PageCategories::getLayoutForCategory($article['category']);

	}

	public static function setDefaultLayout($layout) {
		if (!self::$settings)
			self::$settings = new Settings("Articles");

		self::$settings->setValue("layout", $layout);
	}

	public static function runInheritedWidgets($pageid, $footer, $slot = VirtualPages::PAGEAREA) {
		PageCategories::runCategoryWidgets(self::$database->selectField("instances",
			Array("page" => $pageid), "category"),
			$footer, $slot);
	}

	public static function initializeInheritedWidgets($pageid, $footer, $slot = VirtualPages::PAGEAREA) {
		PageCategories::runCategoryWidgets(self::$database->selectField("instances",
			Array("page" => $pageid), "category"),
			$footer, $slot, VirtualPages::HEADER);
	}

}
Articles::init();
?>
