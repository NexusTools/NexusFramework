<?php

$sort = ControlPanel::getPreference('s', -1);
$desc = ControlPanel::getPreference('r', 0);
$page = ControlPanel::getPreference('p', 0);
$filter = preg_replace('/[^\d\w\s\.@\:\'"><=\-]/i', '', ControlPanel::getPreference('f', ""));
$urlFilter = preg_replace('/[^\d\w\s\.@\:\'"><=\-]/i', '', $_GET['z']);
$paramStart = "p=$page";
if ($filter)
	$paramStart .= "&f=".urlencode($filter);
if ($urlFilter)
	$paramStart .= "&z=".urlencode($urlFilter);

if (isset($_GET['s'])) {
	$sortJSON = ", s: ".$_GET['s'];
	if (isset($_GET['r']) && $_GET['r'])
		$sortJSON .= ", r: 1";
} else
	$sortJSON = "";

foreach ($_GET as $key => $value) {
	if ($key == "s" || $key == "f" || $key == "p" || $key == "r" || $key == "t")
		continue;
	$paramStart .= "&$key=".urlencode($value);
	$sortJSON .= ", $key: ".htmlspecialchars("\"$value\"");
}

if (isset($_GET['t'])) {
	$entry = $database->selectRow($table, Array($idField => $_GET['t']));
	if ($entry) {
		$published = !$entry['published'];
		if ($database->update($table, Array("published" => $published), Array($idField => $_GET['t'])))
			echo "<banner class=\"success\">".($published ? "Published" : "Revoked")." entry `".$entry[$sortField]."`.</banner>";
	} else
		echo "<banner class=\"error\">Attempted to toggle unknown entry.</banner>";
}

echo "<pagebuttons>";

$extraFields = Triggers::broadcast("ControlPanel", "Manage Table Fields",
	Array(ControlPanel::getActiveSection(),
	ControlPanel::getActivePage(),
	$database->getName(),
	$table));
$fields = array_merge($fields, $extraFields);

if ($database instanceof Database && !array_key_exists("created", $fields) && !array_key_exists("created-by", $fields) && !array_key_exists("modified", $fields) && !array_key_exists("modified-by", $fields)) {
	$fields["created"] = Array("render" => "StringFormat::formatDate");
	$fields["created-by"] = Array("render" => "User::getFullNameByID");
	$fields["modified"] = Array("render" => "StringFormat::formatDate");
	$fields["modified-by"] = Array("render" => "User::getFullNameByID");
}

$extraButtons = Triggers::broadcast("ControlPanel", "Database Buttons",
	Array(ControlPanel::getActiveSection(),
	ControlPanel::getActivePage(),
	$database->getName(),
	$table));
foreach ($extraButtons as $button)
	call_user_func_array("ControlPanel::renderStockButton", $button);

if (is_array($stockButtons)) {
	foreach ($stockButtons as $name => $data) {
		if (is_numeric($name)) // Array Index
			self::renderStockButton($data);
		else
			if (is_string($data))
				self::renderStockButton($name, $data);
			else {
				if (isset($data['page'])) {
					if (isset($data['popup']))
						$data['script'] = "ControlPanel.loadPopup(\"".self::$currentSection."\", \"$data[page]\");";
					else
						$data['script'] = "ControlPanel.loadPage(\"".self::$currentSection."\", \"$data[page]\");";
				}
				self::renderStockButton($name,
					isset($data['script']) ? $data['script'] : false,
					isset($data['text']) ? $data['text'] : false);
			}
	}
} else
	echo $stockButtons;

echo "<input value=\"";
echo htmlspecialchars($filter);
echo "\" onkeypress=\"if(event.keyCode == 13){ControlPanel.loadPage('";
echo self::$currentSection;
echo "', '";
echo self::$currentPage;
echo "', {f: this.value$sortJSON});}\" placeholder=\"Filter by ";
echo StringFormat::displayForID($sortField);
echo "\" type=\"text\" class=\"text\">";
echo "</pagebuttons>";

echo "<table style=><tr>";
if ($publishable)
	echo "<th></th>";
$fieldID = 0;
$realFields = false;
if (is_callable(Array($database, "listColumns")))
	$realFields = $database->listColumns($table);
foreach ($fields as $field => $data) {
	if (is_string($data)) {
		$column = $data;
		$display = StringFormat::displayForID($data);
	} else {
		$column = $field;
		if (isset($data['display']))
			$display = $data['display'];
		else
			$display = StringFormat::displayForID($field);
	}

	echo "<th style=\"white-space: nowrap;\"";
	if ($column == "icon") {
		echo " class=\"icon\"";
		$display = "";
	}
	echo '>';
	if ($realFields === false || in_array($field, $realFields)) {
		echo "<a href=\"";
		if (!startsWith(self::$currentSection, "/"))
			echo "control://";
		echo self::$currentSection;
		echo "/";
		echo self::$currentPage;
		echo "?$paramStart&s=";
		if ($sort == $fieldID) {
			if ($desc)
				echo "-1";
			else
				echo "$fieldID&r=1";
		} else
			echo "$fieldID&r=0";
		echo "\">$display";
		if ($sort == $fieldID) {
			if ($desc)
				echo " ▼";
			else
				echo " ▲";

			$sort = $column;
		}
		echo "</a>";
	} else
		echo $display;
	echo "</th>";
	$fieldID++;
}
echo "<th style=\"width: 40%\">Actions</th></tr>";

if (!is_numeric($sort))
	$sort = Array($sort, $desc ? "DESC" : "ASC");
else
	$sort = false;

$alt = true;
if ($page < 0)
	$page = 0;
$start = $page * 15;
$where = Triggers::broadcast("ControlPanel", "Database Query Filter",
	Array(ControlPanel::getActiveSection(),
	ControlPanel::getActivePage(),
	$database->getName(),
	$table));

function importFilter(&$where, $filter) {
	if (!$filter)
		return;

	preg_match_all("/(\w+)([:><]=?)(\"[^\"]|'[^']|[\w\d]+)/", $filter, $advFilter);
	$filter = preg_replace("/\s*(\w+)([:><]=?)(\"[^\"]\"|'[^']'|[\w\d]+)\s*/", "", $filter);
	if ($filter)
		$where["LIKE"] = "%$filter%";

	$i = 0;
	foreach ($advFilter[1] as $key) {
		$val = $advFilter[3][$i];
		$compareType = $advFilter[2][$i];
		$val = preg_replace("/^['\"]/", "", $val);
		if ($compareType != ":")
			$key = "$compareType $key";
		$where[$key] = $val;
		$i++;
	}
}
importFilter($where, $filter);
importFilter($where, $urlFilter);
if (array_key_exists("LIKE", $where)) {
	$where["LIKE $sortField"] = $where["LIKE"];
	unset($where["LIKE"]);
}

$actionPage = false;
if (is_array($actions)) {
	$actions = array_merge($actions, Triggers::broadcast("ControlPanel", "Database Entry Actions",
		Array(ControlPanel::getActiveSection(),
		ControlPanel::getActivePage(),
		$database->getName(),
		$table)));

	foreach ($actions as $action => $link) {
		$actionPage = $link;
		break;
	}
}

$query = $database->queryRows($table, $where, $start, 15, $sort);
if ($query['total'] > 0 && $start >= $query['total']) {
	$page = floor($query['total'] / 15);
	$start = $page * 15;
	$query = $database->queryRows($table, $where, $start, 15, $sort);
}

function url_array_walk(&$value, $key) {
	$value = urlencode($value);
}

if (count($query['results']))
	foreach ($query['results'] as $entry) {
		if (is_string($actions))
			foreach (call_user_func($actions, $entry) as $action => $link) {
				$actionPage = $link;
				break;
			}

		echo "<tr";
		if ($actionPage) {
			if ($alt = !$alt)
				echo " class=\"alt link\"";
			else
				echo " class=\"link\"";
			echo " action=\"";
			echo htmlspecialchars(interpolate($actionPage, false, $entry));
			echo "\" ";
		} else
			if ($alt = !$alt)
				echo " class=\"alt\"";
		echo ">";
		if ($publishable) {
			// TODO: Add way to publish/unpublish
			// ControlPanel.loadPage('Pages', 'Manage', {filter: '$filter', page: $page, toggle: $entry[rowid]})
			echo "<td class=\"icon\"><a href=\"control://";
			echo self::$currentSection;
			echo "/";
			echo self::$currentPage;
			echo "?$paramStart&t=".$entry[$idField];
			echo "\"><img style=\"cursor: pointer\" title=\"";
			echo $entry['published'] ? "Published" : "Unpublished";
			echo "\" src=\"";
			echo self::getStockIcon($entry['published'] ? "enabled" : "disabled");
			echo "\" /></a></td>";
		}
		foreach ($fields as $field => $data) {
			if (is_numeric($field) && is_string($data)) {
				$field = $data;
				$data = null;
			}

			if (is_array($data) && isset($data['render']))
				$value = nl2br(htmlentities(call_user_func($data['render'], isset($data['value']) ? interpolate($data['value'], false, $entry) : $entry[$field])));
			else
				if (isset($data['render-html']))
					$value = call_user_func($data['render-html'], $entry);
				else
					if (isset($data['render-html-field']))
						$value = call_user_func($data['render-html-field'], $entry[$field]);
					else
						if (isset($data['value']))
							$value = nl2br(interpolate(htmlentities($data['value']), false, array_merge($entry, Array(
								"b" => "<strong>", "endb" => "</strong>",
								"small" => "<span style=\"font-size: 10px\">",
								"endsmall" => "</span>"
							))));
						else
							if (isset($data['value-html']))
								$value = nl2br(interpolate($data['value-html'], false, array_merge($entry, Array(
									"b" => "<strong>", "endb" => "</strong>",
									"small" => "<span style=\"font-size: 10px\">",
									"endsmall" => "</span>"
								))));
							else
								if (is_array($data) && isset($data['render-adv']))
									$value = nl2br(htmlentities(call_user_func($data['render-adv'], $entry)));
								else
									$value = nl2br(htmlentities($entry[$field]));

			if ($field == "icon") {
				$value = "<img src=\"".htmlspecialchars($value)."\" width=\"16\" height=\"16\" />";
				if (isset($data['align']))
					echo "<td class=\"icon\" align=\"$data[align]\">$value</td>";
				else
					echo "<td class=\"icon\">$value</td>";
			} else {
				if (isset($data['align']))
					echo "<td align=\"$data[align]\">$value</td>";
				else
					echo "<td>$value</td>";
			}
		}
		echo "<td class=\"last\">";
		array_walk($entry, "url_array_walk");

		foreach (is_array($actions) ? $actions : call_user_func($actions, $entry) as $action => $link) {
			echo "<a ";
			if (!startsWith($link, "/"))
				echo "href=\"control://";
			else
				echo "target=\"_blank\" href=\"";
			echo interpolate($link, false, $entry);
			echo "\">$action</a>";
		}
		echo "</td></tr>";
	}
else {
	echo "<tr><td class=\"last\" column=\"".(count($fields) + 1)."\">";
	if (strlen($filter))
		echo "No Results";
	else
		echo "No Entries";
	echo "</td></tr>";
}
echo "</table><center>";

$end = $start + count($query['results']);
if ($page > 0) {
?>
	<input onclick="ControlPanel.loadPage('<?php echo self::$currentSection; ?>', '<?php echo self::$currentPage; ?>', {f: '<?php echo $filter; ?>', p: 0<?php echo $sortJSON; ?>})" type="button" value="First" class="button">
	<input onclick="ControlPanel.loadPage('<?php echo self::$currentSection; ?>', '<?php echo self::$currentPage; ?>', {f: '<?php echo $filter; ?>', p: <?php echo $page-1; echo $sortJSON; ?>})" type="button" value="<<" class="button">
<?php } else { ?>
	<input type="button" value="First" class="button disabled" disabled>
	<input type="button" value="<<" class="button disabled" disabled>
<?php }

echo "&nbsp;&nbsp;&nbsp;";
if ($query['total'] == 0) {
	if (strlen($filter))
		echo "No Results";
	else
		echo "No Entries";
} else {
?>
Showing <?php echo $start + 1; ?> to <?php echo $end; ?> of <?php echo $query['total'];
	if (strlen($filter))
		echo " Results";
	else
		echo " Entries";
}
echo "&nbsp;&nbsp;&nbsp;";

if ($end < $query['total']) {
?>
	<input onclick="ControlPanel.loadPage('<?php echo self::$currentSection; ?>', '<?php echo self::$currentPage; ?>', {f: '<?php echo $filter; ?>', p: <?php echo $page+1; echo $sortJSON; ?>})" type="button" value=">>" class="button">
	<input onclick="ControlPanel.loadPage('<?php echo self::$currentSection; ?>', '<?php echo self::$currentPage; ?>', {f: '<?php echo $filter; ?>', p: <?php echo floor(($query['total']-1) / 15); echo $sortJSON; ?>})" type="button" value="Last" class="button">
<?php } else { ?>
	<input type="button" value=">>" class="button disabled" disabled>
	<input type="button" value="Last" class="button disabled" disabled>
<?php }
echo "</center>";
?>
