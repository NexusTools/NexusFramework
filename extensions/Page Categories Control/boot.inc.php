<?php
ControlPanel::registerPage("Pages", "Default Category", "edit/article-settings.inc.php");
ControlPanel::registerPage("Pages", "Categories", "edit/categories.json", true);
ControlPanel::registerPage("Pages", "Edit Category", "edit/edit-category.json", false);
ControlPanel::registerPage("Pages", "Create Category", "edit/create-category.json", false);
EditCore::registerEditor("category-widgets", "editors/widgets.inc.php");
?>
