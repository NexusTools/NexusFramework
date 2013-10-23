<?php
$page = isset($_GET['p']) ? $_GET['p'] : 0;

if (isset($_GET['swap'])) {
	$parts = explode(",", $_GET['swap']);
	if (count($parts) == 2) {
		if ($hasParenting) {
			$database->update($table, Array("parent" => - 1), Array("parent" => $parts[0]));
			$database->update($table, Array("parent" => $parts[0]), Array("parent" => $parts[1]));
			$database->update($table, Array("parent" => $parts[1]), Array("parent" => - 1));
		}

		$database->update($table, Array($idField => - 1), Array($idField => $parts[0]));
		$database->update($table, Array($idField => $parts[0]), Array($idField => $parts[1]));
		$database->update($table, Array($idField => $parts[1]), Array($idField => - 1));
	}
}

echo "<table><tr>";
$fieldID = 0;
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
	echo "><a href=\"control://";
	echo self::$currentSection;
	echo "/";
	echo self::$currentPage;
	echo "\">$display</a></th>";
	$fieldID++;
}
echo "<th style=\"width: 40%\">Actions</th></tr>";

$alt = true;
$start = $page * 15;

if ($hasParenting)
	$where = array_merge(Array("parent" => ($parent = (isset($_GET['path']) ? $_GET['path'] : 0))), $where);

$query = $database->queryRows($table, $where, $start, 15);

if (count($query['results'])) {
	$lid = -1;
	for ($i = 0; $i < count($query['results']); $i++) {
		if (isset($query['results'][$i + 1]))
			$nid = $query['results'][$i + 1]['rowid'];
		else
			$nid = -1;

		$entry = $query['results'][$i];
		echo "<tr";
		if ($hasParenting) {
			echo " onclick=\"ControlPanel.loadPage('".self::$currentSection."', '".self::$currentPage."', {path: $entry[rowid]})\" class=\"link";
			if ($alt = !$alt)
				echo " alt\"";
			else
				echo "\"";
		} else
			if ($alt = !$alt)
				echo " class=\"alt\"";
		echo ">";
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
					if (isset($data['value']))
						$value = nl2br(interpolate(htmlentities($data['value']), false, array_merge($entry, Array(
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
		if ($hasParenting) {
			echo "<a ";
			echo "href=\"control://";
			echo self::$currentSection;
			echo "/";
			echo self::$currentPage;
			echo "?path=$entry[rowid]\"";
			echo ">Children</a>&nbsp;&nbsp;&nbsp;<a ";
			if ($lid > 0) {
				echo "href=\"control://";
				echo self::$currentSection;
				echo "/";
				echo self::$currentPage;
				echo "?swap=$lid,$entry[rowid]&path=$parent\"";
			} else
				echo "style=\"color: gray\"";
			echo ">Move Up</a>&nbsp;&nbsp;&nbsp;";
			echo "<a ";
			if ($nid > 0) {
				echo "href=\"control://";
				echo self::$currentSection;
				echo "/";
				echo self::$currentPage;
				echo "?swap=$entry[rowid],$nid&path=$parent\"";
			} else
				echo "style=\"color: gray\"";
			echo ">Move Down</a>";
		} else {
			echo "<a ";
			if ($lid > 0) {
				echo "href=\"control://";
				echo self::$currentSection;
				echo "/";
				echo self::$currentPage;
				echo "?swap=$lid,$entry[rowid]\"";
			} else
				echo "style=\"color: gray\"";
			echo ">Move Up</a>&nbsp;&nbsp;&nbsp;";
			echo "<a ";
			if ($nid > 0) {
				echo "href=\"control://";
				echo self::$currentSection;
				echo "/";
				echo self::$currentPage;
				echo "?swap=$entry[rowid],$nid\"";
			} else
				echo "style=\"color: gray\"";
			echo ">Move Down</a>";
		}

		echo "</td></tr>";

		$lid = $entry['rowid'];
	}
} else {
	echo "<tr><td class=\"last\" column=\"".(count($fields) + 1)."\">";
	echo "No Entries";
	echo "</td></tr>";
}
echo "</table><center>";

$end = $start + count($query['results']);
if ($page > 0) {
?>
	<input onclick="ControlPanel.loadPage('<?php echo self::$currentSection; ?>', '<?php echo self::$currentPage; ?>', {p: 0})" type="button" value="First" class="button">
	<input onclick="ControlPanel.loadPage('<?php echo self::$currentSection; ?>', '<?php echo self::$currentPage; ?>', {p: <?php echo $page-1; ?>})" type="button" value="<<" class="button">
<?php } else { ?>
	<input type="button" value="First" class="button disabled" disabled>
	<input type="button" value="<<" class="button disabled" disabled>
<?php }

echo "&nbsp;&nbsp;&nbsp;";
if ($query['total'] == 0) {
	echo "No Entries";
} else {
?>
Showing <?php echo $start + 1; ?> to <?php echo $end; ?> of <?php echo $query['total'];
	echo " Entries";
}
echo "&nbsp;&nbsp;&nbsp;";

if ($end < $query['total']) {
?>
	<input onclick="ControlPanel.loadPage('<?php echo self::$currentSection; ?>', '<?php echo self::$currentPage; ?>', {p: <?php echo $page+1; ?>})" type="button" value=">>" class="button">
	<input onclick="ControlPanel.loadPage('<?php echo self::$currentSection; ?>', '<?php echo self::$currentPage; ?>', {p: <?php echo floor(($query['total']-1) / 15); ?>})" type="button" value="Last" class="button">
<?php } else { ?>
	<input type="button" value=">>" class="button disabled" disabled>
	<input type="button" value="Last" class="button disabled" disabled>
<?php }
echo "</center>";
if (!$hasParenting)
	return;

$bread = Array();
while ($parent > 0) {
	$data = $database->selectRow($table, Array("rowid" => $parent));
	if (isset($data['title']))
		$title = $data['title'];
	else
		if (isset($data['name']))
			$title = $data['name'];
		else
			if (isset($data['text']))
				$title = $data['text'];
			else
				break;

	array_unshift($bread, Array("title" => $title, "action" => "ControlPanel.loadPage('".self::$currentSection."', '".self::$currentPage."', {path: $parent})"));
	$parent = $data['parent'];
}
return $bread;
?>
