<?php
define("DBIERTICNSFR", cleanpath(dirname(__FILE__).DIRSEP."icons").DIRSEP);
function __importExport__ControlPanelWatcher($module, $event, $arguments) {
	switch ($event) {
	case "Header":
		Template::addScript(dirname(__FILE__).DIRSEP."scripts/live-port.js");
		break;

	case "Database Buttons":
		if (is_string($db = $arguments[2]) && is_string($tab = $arguments[3])) {
			$buttons = Array();
			if (ControlPanel::canUserAccessPage('Database', 'Import Table'))
				$buttons[] = Array("Import", "ControlPanel.loadPopup('Database', 'Import Table', {db: '".$db."', tab: '".$tab."'})", false, Framework::getReferenceURI(DBIERTICNSFR."import.png"));
			if (ControlPanel::canUserAccessPage('Database', 'Export Table'))
				$buttons[] = Array("Export", "ControlPanel.loadPopup('Database', 'Export Table', {db: '".$db."', tab: '".$tab."'})", false, Framework::getReferenceURI(DBIERTICNSFR."export.png"));
			return $buttons;
		}
		break;
	}
}

Triggers::watchModule("ControlPanel", "__importExport__ControlPanelWatcher");
ControlPanel::registerPage("Database", "Manage", "edit/manage.json", true);
ControlPanel::registerPage("Database", "Import Table", "edit/import-table.inc.php", false);
ControlPanel::registerPage("Database", "Export Table", "edit/export-table.inc.php", false);
ControlPanel::registerPage("Database", "Database Definition", "edit/definition.inc.php", false);
?>
