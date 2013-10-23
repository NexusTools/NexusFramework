<?php
set_time_limit(0);
$database = isset($_GET['db']) ? $_GET['db'] : $_POST['db'];
$table = isset($_GET['tab']) ? $_GET['tab'] : $_POST['tab'];
?><center><h2>Export Table</h2><?php
if (!$database) {
	echo "<h4>No Database Specified</h4>";
	return;
}
$db = Database::getInstance($database);
if (!$db->isValid()) {
	echo "<h4>Invalid Database</h4>";
	return;
}

global $__exportDatabaseInstance__DBMaster;
$__exportDatabaseInstance__DBMaster = $db;
function encodeData($value, $type, $meta) {
	global $__exportDatabaseInstance__DBMaster;
	switch ($type) {
	case "INTEGER":
		if (isset($meta['reference']) && $value > 0) { // Resolve Reference
			$meta = $meta['reference'];
			if (is_array($meta)) {
				$refDb = isset($meta['database']) ? Database::getInstance($meta['database']) : $__exportDatabaseInstance__DBMaster;
				$refTable = isset($meta['table']) ? $meta['table'] : $table;
				$refField = isset($meta['field']) ? $meta['field'] : "name";
			} else {
				$refDb = $__exportDatabaseInstance__DBMaster;
				$refTable = $meta;
				$refField = "name";
			}

			$def = $refDb->getDefinition();
			$def = $def[$refTable];

			if (is_array($def['fields']) && is_string($def['parent-field'])) {
				$refId = $value * 1;
				$nextId = $refId;
				$depthLeft = 5;
				$value = "";

				while (($row = $refDb->selectRow($refTable, Array("rowid" => $nextId),
					Array($def['parent-field'], $refField), false)) !== false) {
					$value = $row[1].(strlen($value) ? "/".$value : "");
					$nextId = $row[0];
					$depthLeft--;
					if ($depthLeft < 0) {
						$value = $refId;
						break;
					}
				}
			} else {
				$value = $refDb->selectField($refTable, Array("rowid" => $value), $refField);
				if ($value === false) // Referenced Row Deleted
					$value = null;
			}
		} else
			$value = strval($value);
		break;

	case "BOOLEAN":
		$value = strtolower($value);
		$value = $value > 0;
		break;
	}

	if (is_bool($value))
		return $value ? "Yes" : "No";
	else
		if (is_numeric($value))
			return strval($value);
		else
			return $value;
}
?>
<h4 style="line-height: 100%;">Database: <?php echo $database;
if ($table && isset($_POST['type'])) {
	echo "<br />Table: $table</h4>";
	try {
		$tempPath = TMP_PATH.DIRSEP."exports";
		if (!is_dir($tempPath) && !mkdir($tempPath, 0777, true))
			throw new Exception("Failed to create directory for export: $tempPath");
		$file = fopen($filePath = ($tempPath.DIRSEP.Framework::uniqueHash()), "w");
		if (!$file)
			throw new Exception("Failed to open file for writing: $filePath");
		$mime = "text/".$_POST['type'];
		$filename = StringFormat::idForDisplay($database." ".$table).'.'.$_POST['type'];

		$meta = Array(Array());
		$types = Array("INTEGER");

		$db->lock();
		foreach ($db->getDefinition($table) as $columnDef) {
			if (is_array($columnDef)) {
				array_push($types, $columnDef['type']);
				unset($columnDef['type']);
				array_push($meta, $columnDef);
			} else {
				array_push($types, $columnDef);
				array_push($meta, Array());
			}
		}
		unset($rdef);
		$columns = $db->listColumns($table);
		$omitFields = Array();

		if ($_POST['omitmod']) {
			array_push($omitFields, "modified-by");
			array_push($omitFields, "modified");
		}
		if ($_POST['omitcreat']) {
			array_push($omitFields, "created-by");
			array_push($omitFields, "created");
		}

		foreach ($omitFields as $omitField) {
			$pos = array_search($omitField, $columns);
			if ($pos !== false)
				array_splice($columns, $pos, 1);
		}

		$exportData = $db->select($table, false, $columns, false, false, false, false);

		switch ($_POST['type']) {
		case "csv":
			fputcsv($file, $columns);
			foreach ($exportData as $row) {
				$__exportRowID__DBMaster = $row[0];
				fputcsv($file, array_map("encodeData", $row, $types, $meta));
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
			$mime = "text/sql";
			/*INSERT OR REPLACE INTO Employee (id,role,name)
			 VALUES (  1,
			 'code monkey',
			 (select name from Employee where id = 1)
			 );*/
			$columnString = "";
			fwrite($file, "INSERT OR REPLACE INTO `");
			fwrite($file, $table);
			fwrite($file, "` (");
			$addComma = false;
			foreach ($columns as $column) {
				if ($addComma)
					fwrite($file, ",");
				else
					$addComma = true;
				fwrite($file, "`");
				fwrite($file, $column);
				fwrite($file, "`");
			}
			$addComma = false;
			fwrite($file, ") VALUES \n");

			foreach ($exportData as $row) {
				if ($addComma)
					fwrite($file, ",\n");
				else
					$addComma = true;
				fwrite($file, "(");
				$addInnerComma = false;
				for ($i = 0; $i < count($row); $i++) {
					if ($addInnerComma)
						fwrite($file, ",");
					else
						$addInnerComma = true;

					$data = encodeData($row[$i], $types[$i], $meta[$i]);
					if (is_numeric($data))
						fwrite($file, strval($data));
					else {
						fwrite($file, "'");
						fwrite($file, addslashes($data));
						fwrite($file, "'");
					}
				}
				fwrite($file, ")");
			}
			fwrite($file, ";");
			break;

		default:
			throw new Exception("Unknown Format Specified");
		}
		fclose($file);

		echo "Export Complete,<br />Exports are erased 24 hours after creation.<br /><a target='_blank' href='".Framework::getReferenceURI($filePath, $mime, $filename)."'>Download</a>";
	} catch (Exception $e) {
		if ($file) {
			fclose($file);
			unlink($file);
		}

		echo "An error occured: ".$e->getMessage();
	}
	$db->unlock();
	return;

}
?></h4>
This feature is highly experimental.<br />
Databases that reference eachother, such as the VirtualPages database, cannot be properly exported.<br />
If something goes wrong, it cannot be easily corrected, please make sure you backup the "config" folder before proceeding.
<br /><br />
The progress of an export cannot be measured, for larger databases it may take a very long time.<br />
Please be patient, you will be presented with a download link once the export is complete.
<br /><br /><br /><br />
<form method="POST" action="control://Database/Export Table" style="text-align: left; display: inline-block; width: auto; margin: 0 auto">
<input name="db" value="<?php echo htmlentities($database); ?>" type="hidden" />
Omit Creation Fields<help title="Whether or not to include created-by and created fields in this export.">?</help><br />
<input type="radio" name="omitcreat" id="omitcreatyes" value="1" checked><label for="omitcreatyes">Yes</label> <input type="radio" name="omitcreat" id="omitcreatno" value="0"><label for="omitcreatno">No</label><br />
Omit Modification Fields<help title="Whether or not to include modified-by and modified fields in this export.">?</help><br />
<input type="radio" name="omitmod" id="omitmodyes" value="1" checked><label for="omitmodyes">Yes</label> <input type="radio" name="omitmod" id="omitmodno" value="0"><label for="omitmodno">No</label><br />
Table<br />
<select style="width: 100%;" name="tab"><?php
foreach ($db->listTables() as $t) {
	echo "<option value='$t'";
	if ($table == $t)
		echo " selected";
	echo ">";
	echo StringFormat::displayForID($t);
	if ($table == $t)
		echo " (Current)";
	echo "</option>";
}
?></select><br />
Format<br />
<select style="width: 100%;" name="type"><option value="csv">Comma Separated Values (.cvs)</option>
<option value="odf">ODF Spreadsheet (.ods)</option>
<option value="uos">UOF Spreadsheet (.uos)</option>
<option value="xls">Microsoft Excel 97/2000/2003 (.xls)</option>
<option value="xlsx">Microsoft Excel 2007/2010 (.xlsx)</option>
<option value="sqlbin">SQL Binary (.sqlbin)</option>
<option value="sql">SQL Markup (.sql)</option></select></form>
<?php
ControlPanel::renderStockButton("Export", "ControlPanel.submitForm()", false, Framework::getReferenceURI(DBIERTICNSFR."export.png"));
ControlPanel::renderStockButton("discard", "ControlPanel.closePopup()", "Close");
?></center>
