<?php
define("INAPI", true);

OutputFilter::resetToNative(false);
header("Content-Type: text/plain");
set_time_limit(0);

echo '(';
$data = Array();
$db = false;
try {
	$database = isset($_GET['db']) ? $_GET['db'] : $_POST['db'];
	$table = isset($_GET['tab']) ? $_GET['tab'] : $_POST['tab'];
	if (!$database)
		throw new Exception("No Database Specified");
	if (!$table)
		throw new Exception("No Table Specified");
	$db = Database::getInstance($database);
	if (!$db->isValid())
		throw new Exception("Invalid or Unknown Database");

	global $__importDatabaseInstance__DBMaster;
	$meta = Array();
	$types = Array();
	$db->lock();
	foreach ($db->getDefinition($table) as $field => $columnDef) {
		if (is_array($columnDef)) {
			$types[$field] = $columnDef['type'];
			$meta[$field] = $columnDef;
		} else {
			$types[$field] = $columnDef;
			$meta[$field] = Array();
		}
	}

	global $__importDatabaseTypes__DBMaster;
	global $__importDatabaseMeta__DBMaster;
	$__importDatabaseTypes__DBMaster = $types;
	$__importDatabaseMeta__DBMaster = $meta;
	unset($meta);
	unset($types);

	$__importDatabaseInstance__DBMaster = $db;
	function decodeData(&$value, $key) {
		if ($key == "rowid")
			return;

		global $__importDatabaseInstance__DBMaster;
		global $__importDatabaseTypes__DBMaster;
		global $__importDatabaseMeta__DBMaster;
		$type = $__importDatabaseTypes__DBMaster[$key];
		$meta = $__importDatabaseMeta__DBMaster[$key];

		$db = $__importDatabaseInstance__DBMaster;
		switch ($type) {
		case "INTEGER":
			if (isset($meta['reference']) && !is_numeric($value)) { // Resolve Reference
				$value = trim($value);
				if (!strlen($value))
					return;

				$meta = $meta['reference'];
				if (is_array($meta)) {
					$refDb = isset($meta['database']) ? Database::getInstance($meta['database']) : $__importDatabaseInstance__DBMaster;
					$refTable = isset($meta['table']) ? $meta['table'] : $table;
					$refField = isset($meta['field']) ? $meta['field'] : "name";
				} else {
					$refDb = $__importDatabaseInstance__DBMaster;
					$refTable = $meta;
					$refField = "name";
				}

				$def = $refDb->getDefinition();
				$def = $def[$refTable];

				$value = strtolower($value);
				$compareField = "LOWER(`".$refField."`)";

				if (is_array($def['fields']) && is_string($def['parent-field'])) {
					$parts = explode("/", $value);
					$value = 0;
					foreach ($parts as $part) {
						$value = $refDb->selectField($refTable, Array($compareField => $part, "parent" => $value), "rowid");
						if ($value === false) { // Category Unfound...
							$value = 0;
							return;
						}
					}

				} else
					$value = $refDb->selectField($refTable, Array($compareField => $value), "rowid");

			} else
				if (!is_numeric($value))
					$value = 0;

			return;

		case "BOOLEAN":
			if (is_numeric($value))
				$value = $value > 0;
			else
				$value = in_array(strtolower($value), Array("yes", "true", "on", "okay"));
			return;
		}
	}

	$mode = $_POST['mode'] * 1;
	$clear = $_POST['clear'] * 1 === 1;
	if ($clear && $mode == 2)
		throw new Exception("Cannot update rows if clearing the table first");

	$type = $_POST['type'];
	if ($type == "*")
		$type = pathinfo($_FILES['dataFile']['name'], PATHINFO_EXTENSION);

	$file = fopen($_FILES['dataFile']['tmp_name'], "r");
	if (!$file)
		throw new Exception("Failed to read uploaded file");

	if ($clear)
		$totalRows = $db->countRows($table);
	$output = Array("inserts" => 0, "updates" => Array("rows" => 0, "entries" => 0),
		"errors" => 0, "error-messages" => Array());

	switch ($type) {
	case "csv":
		//if($mode == 1)
		//    $db->delete($table);
		$fields = false;
		while (is_array($data = fgetcsv($file))) {
			if (!count($data) || $data[0] === null)
				continue;
			if (!$fields) {
				$fields = $data;
				$realFields = $db->listColumns($table);
				foreach ($fields as $field) {
					if (!in_array($field, $realFields))
						throw new Exception("Field `$field` does not exist in the table `".StringFormat::displayForID($table)."` of database `$database`.");
				}
				unset($realFields);

				if ($clear)
					$db->delete($table);
				continue;
			}

			$data = array_combine($fields, $data);
			if ($data === false)
				$output['errors']++;
			else {
				array_walk($data, "decodeData");
				if (!isset($data['rowid']) || ($existingRow = $db->selectRow($table, $where = Array("rowid" => $data['rowid']),
					$fields)) === false) {

					if ($mode == 2)
						continue;

					if ($db->insert($table, $data))
						$output['inserts']++;
					else {
						$output['errors']++;
						array_push($output['error-messages'], Array($db->lastError(), $db->lastQuery()));
					}
				} else {
					if ($mode == 1)
						continue;

					unset($data['rowid']);
					$changes = Array();
					foreach ($data as $key => $value)
						if ($value != $existingRow[$key])
							$changes[$key] = $value;
					if (!count($changes))
						continue;

					if ($db->update($table, $changes, $where)) {
						$output['updates']['rows']++;
						$output['updates']['entries'] += count($changes);
					} else {
						$output['errors']++;
						array_push($output['error-messages'], Array($db->lastError(), $db->lastQuery()));
					}
				}
			}
		}
		break;

	case "odf":
	case "uos":
		throw new Exception("Open Spreadsheet Formats are not yet implemented");

	case "sqlbin":
		throw new Exception("The SQL Binary Format is not yet implemented");

	case "xls":
	case "xlsx":
		throw new Exception("Microsoft Excel formats are not yet implemented");

	case "sql":
		throw new Exception("SQL is not implemented for importing");
		break;

	default:
		throw new Exception("Unknown Format");
	}
	$data['text'] = "";

	if ($clear) {
		$change = $db->countRows($table) - $totalRows;
		$data['text'] .= "Table Cleared<br />";
		if ($change > 0)
			$data['text'] .= $change." More Rows than Old Table";
		else
			if ($change < 0)
				$data['text'] .= (-$change)." Less Rows than Old Table";

		if ($output['inserts']) {
			if (strlen($data['text']))
				$data['text'] .= "<br />";
			$data['text'] .= $output['inserts']." Rows Imported";
		}
	} else
		if ($output['inserts'])
			$data['text'] .= $output['inserts']." Rows Inserted";
	if ($output['updates']['rows']) {
		if (strlen($data['text']))
			$data['text'] .= "<br />";
		$data['text'] .= $output['updates']['entries']." Entries in ".$output['updates']['rows']." Rows Updated";
	}
	if ($output['errors']) {
		if (strlen($data['text']))
			$data['text'] .= "<br />";
		$data['text'] .= $output['errors']." Entries Failed";
	}

	if (!strlen($data['text']))
		$data['text'] .= "Nothing was changed";
	else
		$data['text'] = "Detailed Results<br /><br />".$data['text'];
	$data['error-messages'] = $output['error-messages'];
} catch (Exception $e) {
	$data = Array("error" => nl2br($e->getMessage()), "request" => $_POST, "type" => $type, "db" => $database, "tab" => $table);
}
if ($db)
	$db->unlock();
$data["db"] = $database;
$data["tab"] = $table;
echo json_encode($data);
die(')');
?>
