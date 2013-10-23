<?php
function __renderFileIcon($entry) {
	if (startsWith($entry['mime'], "image/")) {
		try {
			if (class_exists("ModifiedImage"))
				return ModifiedImage::scaledTransparentURI($entry['path'], 16, 16, ModifiedImage::KeepAspectRatio);
			else
				throw new Exception("Image Library Found");
		} catch (Exception $e) {
			return Framework::getReferenceURI($entry['path']);
		}
	}

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

	case "application/zip":
		return ControlPanel::getStockIcon("archive");

	case "application/x-sharedlib":
	case "application/x-executable":
		return ControlPanel::getStockIcon("executable");

	case "text/x-pascal":
	case "text/x-shellscript":
	case "text/x-perc":
	case "text/x-c":
	case "text/x-php":
		return ControlPanel::getStockIcon("script");

	case "application/pdf":
		return ControlPanel::getStockIcon("pdf");

	case "application/x-sqlite":
		return ControlPanel::getStockIcon("sql");

	default:
		return ControlPanel::getStockIcon("file");

	}
}

function __renderFileNameLink($entry) {
	if (!is_readable($entry['path']))
		$end = "<br /><span style=\"color: red; font-size: 9px;\">No Access</span>";
	else
		if (!is_writable($entry['path']))
			$end = "<br /><span style=\"color: red; font-size: 9px;\">Read Only</span>";
		else
			$end = "";

	if ($entry['target'])
		$end = "$end<br /><span style=\"color: gray; font-size: 10px;\">Link Target: $entry[target]</span>";

	return "$entry[name]$end";
}

function __fileActions($entry) {
	if (!is_readable($entry['path']))
		return Array("Fix Permissions" => "Resources/Fix Permissions?path=media/{{uri}}");

	if (!is_writable($entry['path'])) {
		if (is_dir($entry['path']))
			return Array(
				"Explore" => "Resources/Media?path={{uri}}",
				//"Download as Archive" => "Resources/Create Archive?file=media/{{uri}}",
				"Fix Permissions" => "Resources/Fix Permissions?path=media/{{uri}}"
			);
		return Array(
			"View" => "Resources/View?file=media/{{uri}}",
			"Download" => Framework::getReferenceURI($entry['path']),
			//"Download in Archive" => "Resources/Create Archive?file=media/{{uri}}",
			"Fix Permissions" => "Resources/Fix Permissions?path=media/{{uri}}"
		);
	}

	if (is_dir($entry['path']))
		return Array(
			"Explore" => "Resources/Media?path={{uri}}",
			//"Download as Archive" => "Resources/Create Archive?file=media/{{uri}}",
			"Delete" => "Resources/Delete?file=media/{{uri}}"
		);

	return Array(
		"Edit" => "Resources/Edit?file={{uri}}",
		"Download" => Framework::getReferenceURI($entry['path']),
		//"Download in Archive" => "Resources/Create Archive?file=media/{{uri}}",
		"Delete" => "Resources/Delete?file=media/{{uri}}"
	);
}

function __renderFileSize($entry) {
	if (!is_readable($entry['path']))
		return "N/A";

	if (is_dir($entry['path'])) {
		$dir = opendir($entry['path']);
		$files = 0;
		$folders = 0;
		while (($file = readdir($dir)) !== false) {
			if ($file == "." || $file == ".." || startsWith($file, ".") || endsWith($file, "~"))
				continue;

			if (is_dir($entry['path'].DIRSEP.$file))
				$folders++;
			else
				$files++;
		}
		closedir($dir);

		if ($files > 0) {
			$data = "$files file";
			if ($files > 1)
				$data .= 's';
		} else
			$data = false;

		if ($folders > 0) {
			if ($data)
				$data .= "\n";
			else
				$data = "";
			$data .= "$folders folder";
			if ($folders > 1)
				$data .= 's';
		} else
			if (!$data)
				$data = "Empty";

		return $data;
	}
	return StringFormat::formatFilesize($entry['size']);
}

$path = isset($_GET['path']) ? $_GET['path'] : "";

if (isset($_GET['path']))
	$fs = new FilesystemDatabase(INDEX_PATH."media", $_GET['path']);
else
	$fs = new FilesystemDatabase(INDEX_PATH."media");

if (!is_readable($fs->getFilepath())) {
	return;
}

if (!($canWrite = is_writable($fs->getFilepath())))
	echo "<banner class=\"error\">PHP does not have write access to this folder.</banner>";

if ($canWrite)
	echo "<filedrop folder=\"^/media/$path\">";
ControlPanel::renderManagePage($fs, "filesystem", Array("icon" => Array(
	"render-adv" => "__renderFileIcon"
), "name" => Array(
	"render-html" => "__renderFileNameLink"
), "mime", "ctime" => Array(
	"render" => "StringFormat::formatDate",
	"display" => "Created"
), "size" => Array(
	"render-adv" => "__renderFileSize",
	"align" => "right"
)), "__fileActions", false, $canWrite ? Array(
	"new" => Array(
		"text" => "Upload",
		"page" => "Upload"
	),
	"create" => Array(
		"page" => "Create"
	) //,
	//"archive" => Array(
	//	"text" => "Download in Archive"
	//)
	
) : Array(
	"permissions" => Array(
		"text" => "Fix Permissions"
	) //,
	//"archive" => Array(
	//	"text" => "Download in Archive"
	//)
	
));

if ($canWrite)
	echo "</filedrop>";
if (isset($_GET['path'])) {
	$breadcrumb = Array();
	$uri = "";
	foreach (Framework::splitPath($_GET['path']) as $path) {
		if (strlen($uri))
			$uri .= "/";
		$uri .= $path;
		array_push($breadcrumb, Array("title" => $path, "action" => "ControlPanel.loadPage('Resources', 'Media', {path: '$uri'})"));
	}
	return $breadcrumb;
}
?>
