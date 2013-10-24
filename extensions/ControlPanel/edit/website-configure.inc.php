<pagebuttons><?php
ControlPanel::renderStockButton("apply");
ControlPanel::renderStockButton("discard", "ControlPanel.loadPage('Website', 'Configure')");
if (User::isSuperAdmin()) {
	ControlPanel::renderStockButton("Root Password", "ControlPanel.loadPopup('Website', 'Change Root Password')", false, ControlPanel::getStockIcon("locked"));
}
?></pagebuttons><?php

$defines = Array();

$script = isset($_POST['script']) ? $_POST['script'] : false;
$handle = @fopen(INDEX_PATH."framework.config.php", "r");
if ($handle) {
	while (($buffer = fgets($handle, 4096)) !== false) {
		$buffer = trim($buffer);
		if (startsWith($buffer, "<?"))
			break;
	}

	while (($buffer = fgets($handle, 4096)) !== false) {
		$buffer = trim($buffer);
		if (!strlen($buffer))
			continue;

		if (startsWith($buffer, "define")) {
			preg_match('/define\("(.*?)", "(.*?)"\)/', $buffer, $match);
			$defines[$match[1]] = $match[2];
			continue;
		}

		break;
	}

	if (!$script) {
		$script = "";
		while (($buffer = fgets($handle, 4096)) !== false) {
			if (startsWith(trim($buffer), "?>"))
				break;

			$script .= $buffer;
		}
	}
	fclose($handle);
}

$script = trim($script);

if (isset($_POST['title_format'])) {
	$defines['TITLE_FORMAT'] = $_POST['title_format'];
	$defines['DEFAULT_PAGE_NAME'] = $_POST['default_page_name'];
	$defines['META_DESCRIPTION'] = $_POST['meta_description'];

	$defines['META_KEYWORDS'] = implode(", ", preg_split("/\s*?[\n\r]\s*?/", $_POST['meta_keywords'], 0, PREG_SPLIT_NO_EMPTY));

	$script = $_POST['script'];

	$handle = @fopen(INDEX_PATH."framework.config.php", "w");
	fwrite($handle, "<?php // DO NOT MANUALLY MODIFY THIS FILE\n\n");
	foreach ($defines as $key => $val) {
		fwrite($handle, "define(\"$key\", \"$val\");\n");
	}
	fwrite($handle, "\n\n// USER LOADER SCRIPT\n");
	fwrite($handle, $script);
	fwrite($handle, "\n?>");

	echo "<banner class=\"success\">Changes Applied, Updated `framework.conf.php`.</banner>";
}
?>

<form method="post" action="control://Website/Configure">
<groupbox style="text-align: center;"><label>General</label>
<table style="height: 100%;"><tr><td>Title Format</td><td>Default Page Title</td></tr>
<tr>
	<td valign="top"><input name="title_format" value="<?php echo $defines['TITLE_FORMAT']; ?>" type="text" class="text"></td>
	<td valign="top"><input name="default_page_name" value="<?php echo $defines['DEFAULT_PAGE_NAME']; ?>" type="text" class="text"></td>
</tr><tr><td colspan="2" style="padding-top: 20px;" align="center" valign="bottom"><input type="button" class="button" value="Advanced Options" onclick="ControlPanel.loadPopup('Website', 'Advanced Options')" /></td></tr></table>

</groupbox>
<groupbox style="text-align: center;">
<label>MetaTags</label>
<table><tr><td>Description</td><td>Keywords</td></tr>
<tr>
	<td valign="top"><input name="meta_description" value="<?php echo $defines['META_DESCRIPTION']; ?>" type="text" class="text"></td>
	<td rowspan="2"><textarea name="meta_keywords" style="height: 80px; resize:none;"><?php
foreach (explode(",", $defines['META_KEYWORDS']) as $keyword)
	echo trim($keyword)."\n";
?></textarea></td></tr>
<tr><td valign="bottom"><input type="button" class="button" onclick="ControlPanel.loadPopup('Website', 'Advanced Meta Tags')" value="Advanced Tags"></td></tr></table>
</groupbox><br />

<groupbox style="width: 730px; text-align: center;">
<label>Loader Script</label>
<textarea name="script" style="width: 100%; height: 300px; box-sizing: border-box; resize:none;"><?php
echo htmlentities($script);
?></textarea>
</groupbox></form>
