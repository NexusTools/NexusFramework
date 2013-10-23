<?php
$productCatID = PageCategories::resolveCategoryID("Products");
PageCategories::runCategoryWidgets($productCatID, false, VirtualPages::RIGHTCOLUMN, VirtualPages::HEADER);
PageCategories::runCategoryWidgets($productCatID, true, VirtualPages::RIGHTCOLUMN, VirtualPages::HEADER);
?>
