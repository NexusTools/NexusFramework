<?php
set_time_limit(0);
$database = isset($_GET['db']) ? $_GET['db'] : $_POST['db'];
$table = isset($_GET['tab']) ? $_GET['tab'] : $_POST['tab'];
?><center><h2>Import Table</h2><?php
if (!$database) {
	echo "<h4>No Database Specified</h4>";
	return;
}
$db = Database::getInstance($database);
if (!$db->isValid()) {
	echo "<h4>Invalid Database</h4>";
	return;
}
?>
<h4 style="line-height: 100%;">Database: <?php echo $database; ?></h4>
This feature is highly experimental.<br />
Databases that reference eachother, such as the VirtualPages database, cannot be properly imported.
<br /><br />
The progress of an import cannot be measured, for larger databases it may take a very long time.<br />
<b>It is highly recommended you backup the config folder before proceeding</b>
<br /><br /><br /><br />
<form method="POST" action="control://Database/Export" style="text-align: left; display: inline-block; width: auto; margin: 0 auto">
<input name="db" value="<?php echo htmlentities($database); ?>" type="hidden" />

File<br />
<input type="file" name="dataFile" /><br />
Clear First<help title="This will clear the table before entering new data, this effectively destroys any existing data and cannot be undone.">?</help><br />
<input type="radio" name="clear" id="clearyes" value="1"><label for="clearyes">Yes</label> <input type="radio" name="clear" id="clesrno" value="0" checked><label for="clesrno">No</label><br />
Mode<br />
<select style="width: 100%" name="mode">
<option value="0">Create and Update Rows and Entries</option>
<option value="1">Only Create Missing Rows</option>
<option value="2">Only Update Existing Entries</option>
</select>
Table<br />
<select style="width: 100%;" name="tab">
<?php
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
<select style="width: 100%;" name="type">
<option value="*">Detect by Extension (.*)</option>
<option value="csv">Comma Separated Values (.cvs)</option>
<option value="odf">ODF Spreadsheet (.ods)</option>
<option value="uos">UOF Spreadsheet (.uos)</option>
<option value="xls">Microsoft Excel 97/2000/2003 (.xls)</option>
<option value="xlsx">Microsoft Excel 2007/2010 (.xlsx)</option>
<option value="sqlbin">SQL Binary (.sqlbin)</option>
<option value="sql">SQL Markup (.sql)</option></select></form>
<?php
ControlPanel::renderStockButton("Import", "ControlPanel.importTableData()", false, Framework::getReferenceURI(DBIERTICNSFR."import.png"));
ControlPanel::renderStockButton("discard", "ControlPanel.closePopup()", "Close");
?></center>
