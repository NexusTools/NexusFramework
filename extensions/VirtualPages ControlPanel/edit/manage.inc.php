<?php

if (isset($_GET['delete'])) {
	$tpage = VirtualPages::fetchPage($_GET['delete']);
	if ($tpage) {
		VirtualPages::dropPage($tpage['rowid']);
		echo "<banner class=\"success\">Deleted page `$tpage[title]`</banner><br />";
	}
} else
	if (isset($_GET['toggle']))
		VirtualPages::togglePublished($_GET['toggle']);
	else
		if (isset($_GET['delwidget'])) {
			$widget = VirtualPages::fetchWidget($_GET['delwidget']);
			if ($widget) {
				VirtualPages::dropWidget($widget['rowid']);
				echo "<banner class=\"success\">Widget Deleted</banner><br />";
			}
		}

ControlPanel::renderManagePage(VirtualPages::getDatabase(), "pages", Array("title" => Array(
	"value-html" => "{{title}} {{small}}({{type}}){{endsmall}}\n{{small}}<a target='_blank' href='{{BASE_URL}}{{path}}'>{{path}}</a>{{endsmall}}"
),
	"condition" => Array(
		"display" => "Visibility",
		"render" => "StringFormat::formatCondition"
	),
	"created" => Array(
		"render" => "StringFormat::formatDate"
	),
	"created-by" => Array(
		"render" => "User::getFullNameByID"
	),
	"modified" => Array(
		"render" => "StringFormat::formatDate"
	),
	"modified-by" => Array(
		"render" => "User::getFullNameByID"
	)), Array(
	"Edit" => "Pages/Edit?id={{rowid}}",
	"Delete" => "Pages/Delete?id={{rowid}}&popup=true"
), true, Array(
	"new" => Array(
		"text" => "Create",
		"page" => "Create"
	)
), "title");
?>
