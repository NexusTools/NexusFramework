<?php

$processedFields = Array();

foreach ($fields as $field => $data) {
	$type = false;
	$help = false;
	if (is_numeric($field)) {
		$name = $data;
		$title = StringFormat::displayForID($data);
		$meta = Array();
	} else {
		$name = $field;
		$title = isset($data['title']) ? $data['title'] : StringFormat::displayForID($field);
		if (isset($data['type']))
			$type = $data['type'];
		if (isset($data['help']))
			$help = $data['help'];
		$meta = $data;
	}

	if (!$type)
		switch ($title) {
		case "Username":
		case "Name":
		case "Title":
			$type = "title";
			break;

		case "Layout":
			$type = "layout";
			break;

		case "Parent":
			$type = "parent-selector";
			break;

		case "Password":
			$type = "password";
			break;

		case "Email":
			$type = "email";
			break;

		case "Condition":
			$type = "condition";
			break;

		default:
			$type = "line";
		}

	$meta['rowid'] = $id;
	$meta['help'] = $help;
	$meta['type'] = $type;
	$meta['title'] = $title;
	$processedFields[$name] = $meta;
}
$fields = $processedFields;
unset($processedFields);

if (!function_exists("fetch_posted_values")) {
	function fetch_posted_values(&$errors, &$fields, $encode = true) {
		$values = Array();
		foreach ($fields as $field => $data) {
			if (array_key_exists("readonly", $data))
				continue;

			if (is_numeric($field))
				$field = $data;

			if (isset($_POST[$field])) {
				$value = $_POST[$field];
				if (isset($meta['encode']))
					$value = call_user_func($meta['encode'], $value);
				$meta = $fields[$field];
				if (is_string($msg = EditCore::validate($meta['type'], $field, $value, $meta)))
					$errors[$field] = $msg;
				else
					if ($encode)
						$values[$field] = EditCore::encode($meta['type'], $value, $meta);
					else
						$values[$field] = $value;
			} else
				$errors[$field] = "Missing from POST Data";
		}

		return $values;
	}
}

$values = false;
$errors = Array();
if (isset($_POST['action'])) {
	$action = $_POST['action'];
	$entryId = -1;

	switch ($action) {
	case "save":
		if ($id === - 1)
			break;

		$entryId = $id;
		$values = fetch_posted_values($errors, $fields);
		if (!count($errors) && !$database->update($table, $values, Array("rowid" => $id)))
			$errors['database'] = $database->lastError();
		break;

	case "create":
		$values = fetch_posted_values($errors, $fields);
		if (!count($errors)) {
			$_GET['id'] = $database->insert($table, $values);
			if (!$_GET['id'])
				$errors['database'] = $database->lastError();
			else
				$entryId = $_GET['id'];

		}
		break;

	case "publish":
		$values = fetch_posted_values($errors, $fields);
		if (!count($errors)) {
			$values['published'] = true;
			$_GET['id'] = $database->insert($table, $values);
			if (!$_GET['id'])
				$errors['database'] = $database->lastError();
			else
				$entryId = $_GET['id'];
		}
		break;

	case "discard":
		ControlPanel::changePage($pages['discard']['page']);
		return;
		break;

	case "save-close":
		if ($id === - 1)
			break;

		$values = fetch_posted_values($errors, $fields);
		$entryId = $id;
		if (!count($errors) && !$database->update($table, $values, Array("rowid" => $id)))
			$errors['database'] = $database->lastError();

		break;

	}

	if (!count($errors)) {
		if (isset($pages[$action])) {
			if (isset($pages[$action]['callback']))
				call_user_func($pages[$action]['callback'], $entryId);

			if (isset($pages[$action]['message']))
				echo "<banner class=\"success\">".interpolate($pages[$action]['message'], $values)."</banner>";

			if (isset($pages[$action]['page'])) {
				$_POST = Array();
				ControlPanel::changePage($pages[$action]['page']);
				return;
			}
		}
	}
}

if ($values)
	$values = fetch_posted_values($errors, $fields, false);
else {
	if ($id == - 1)
		$values = $_POST;
	else {
		$values = $database->selectRow($table, Array("rowid" => $id));
		if (!$values)
			throw $database->lastException();
	}
}

if (isset($_GET['del'])) {
	if ($_GET['del'] === "yes") {
		echo "<banner class=\"success\">".nl2br(interpolate($pages['delete']['message'], false, $values))."</banner>";
		$database->delete($table, Array("rowid" => $id));
		ControlPanel::changePage($pages['delete']['page']);
	} else {
		echo "<center>";
		echo nl2br(interpolate($pages['delete']['question'], false, $values));
		echo "<br /><br />";
		ControlPanel::renderStockButton("delete", "ControlPanel.loadPage('".self::$currentSection."', '".self::$currentPage."', {id: $id, del: 'yes'});", "Delete It!");
		ControlPanel::renderStockButton("Nevermind", "ControlPanel.closePopup();");
		echo "</center>";
	}
	return;
}
?>


<pagebuttons><?php

foreach ($buttons as $button => $def) {
	if (isset($def['script']))
		$script = $def['script'];
	else
		switch ($button) {
		case "delete":
			$script = "ControlPanel.loadPopup('".self::$currentSection."', '".self::$currentPage."', {id: $id, del: false})";
			break;

		case "discard":
			$script = "ControlPanel.loadPage('".self::$currentSection."', '".$pages['discard']['page']."')";
			break;

		default:
			$script = false;
		}
	ControlPanel::renderStockButton($button, $script, isset($def['text']) ? $def['text'] : false);
}
?></pagebuttons><?php

if (array_key_exists("database", $errors)) {
	echo "<banner class='error'>A database error occured:\n<pre>";
	print_r($errors['database']);
	echo "</pre></banner>";
}

echo "<form action=\"control://";
echo self::$currentSection;
echo "/";
echo self::$currentPage;

$first = true;
foreach ($_GET as $key => $value) {
	if ($first) {
		echo "?";
		$first = false;
	} else
		echo "&";
	echo urlencode($key)."=".urlencode($value);
}
echo "\">";

$first = true;
foreach ($fields as $field => $meta) {
	if ($first)
		$first = false;
	else
		echo "<br />";
	if (array_key_exists("readonly", $meta))
		echo "<span style=\"color: gray\">";
	echo "$meta[title]";
	if (array_key_exists("readonly", $meta))
		echo "</span>";

	if ($meta['help']) {
		echo " <help title=\"";
		echo htmlspecialchars($meta['help']);
		echo "\">?</help>";
	}
	echo "<br />";
	if (isset($errors[$field]))
		echo "<span style=\"color:red; font-size: 10px;\">".$errors[$field]."</span><br />";

	EditCore::render($meta['type'], $field, isset($values[$field]) ? (isset($meta['decode']) ? call_user_func($meta['decode'], $values[$field]) : $values[$field]) : (isset($meta['default']) ? $meta['default'] : null), $meta);
}
?>
