<?php
function __domain_EnvironmentLoader($module, $event) {
	if ($event != "page-data")
		return;

	$db = Database::getInstance();
	$data = Array();
	foreach ($db->select("environment", Array("domain" => "")) as $row) {
		$data[$row['key']] = $row['value'];
	}

	foreach ($db->select("environment", Array("domain" => DOMAIN)) as $row) {
		$data[$row['key']] = $row['value'];
	}
	return Array("DomainEnv" => $data);
}
Triggers::watchModule("template", "__domain_EnvironmentLoader");
?>
