<?php
function __renderFileIcon($entry) {
	if (startsWith($entry['mime'], "image/"))
		return Framework::getReferenceURI($entry['path']);

	if (startsWith($entry['mime'], "video/"))
		return ControlPanel::getStockIcon("video");

	if (startsWith($entry['mime'], "audio/"))
		return ControlPanel::getStockIcon("audio");

	switch ($entry['mime']) {
	case "directory":
		return ControlPanel::getStockIcon("folder");

	case "parent":
		return ControlPanel::getStockIcon("go-up");

	case "text/html":
		return ControlPanel::getStockIcon("xml");

	case "text/x-c":
	case "text/x-php":
		return ControlPanel::getStockIcon("script");

	case "application/x-sqlite":
		return ControlPanel::getStockIcon("sql");

	default:
		return ControlPanel::getStockIcon("file");

	}
}

function __renderFileNameLink($entry) {
	if (!is_writable($entry['path']))
		$end = "<br /><span style=\"color: red; font-size: 10px;\">Missing Write Permission</span>";
	else
		$end = false;

	if ($entry['target'])
		$end = ($end ? $end."<br />" : "")."<span style=\"color: gray; font-size: 10px;\">Link Target: $entry[target]</span>";

	if ($entry['mime'] == "directory" || $entry['mime'] == "parent")
		return "<a href=\"control://Resources/Filesystem?path=$entry[uri]\">$entry[name]</a>$end";
	else
		return "<a href=\"control://Resources/View Media?file=$entry[uri]\">$entry[name]</a>$end";
}

function __fileActions($entry) {
	if (!is_readable($entry['path']))
		return Array("Fix Permissions" => "Resources/Fix Permissions?path={{uri}}");

	if (!is_writable($entry['path'])) {
		if (is_dir($entry['path']))
			return Array(
				"Explore" => "Resources/Filesystem?path={{uri}}",
				"Download as Archive" => "Resources/Create Archive?file={{uri}}",
				"Fix Permissions" => "Resources/Fix Permissions?path={{uri}}"
			);
		return Array(
			"View" => "Resources/View?file={{uri}}",
			"Download" => "Resources/Download?file={{uri}}",
			"Download in Archive" => "Resources/Create Archive?file={{uri}}",
			"Fix Permissions" => "Resources/Fix Permissions?path={{uri}}"
		);
	}

	if (is_dir($entry['path']))
		return Array(
			"Explore" => "Resources/Filesystem?path={{uri}}",
			"Download as Archive" => "Resources/Create Archive?file={{uri}}",
			"Delete" => "Resources/Delete?file={{uri}}"
		);

	return Array(
		"Edit" => "Resources/Edit?file={{uri}}",
		"Download" => "Resources/Download?file={{uri}}",
		"Download in Archive" => "Resources/Create Archive?file={{uri}}",
		"Delete" => "Resources/Delete?file={{uri}}"
	);
}

if (isset($_GET['path']))
	$fs = new FilesystemDatabase("/", $_GET['path']);
else
	$fs = new FilesystemDatabase("/");

if (!is_readable($fs->getFilepath())) {
	return;
}

if (!($canWrite = is_writable($fs->getFilepath())))
	echo "<banner class=\"error\">PHP does not have write access to this folder.</banner>";

ControlPanel::renderManagePage($fs, "filesystem", Array("icon" => Array(
	"render-adv" => "__renderFileIcon"
), "name" => Array(
	"render-html" => "__renderFileNameLink"
), "mime", "ctime" => Array(
	"render" => "StringFormat::formatDate",
	"display" => "Created"
), "size" => Array(
	"render" => "StringFormat::formatFilesize",
	"align" => "right"
)), "__fileActions", false, $canWrite ? Array(
	"new" => Array(
		"text" => "Upload",
		"page" => "Upload"
	),
	"create" => Array(
		"page" => "Create"
	),
	"archive" => Array(
		"text" => "Download in Archive"
	)
) : Array(
	"permissions" => Array(
		"text" => "Fix Permissions"
	)
));
?>
