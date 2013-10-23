<?php
if (PageModule::hasError())
	return; // Abort on Error Pages

$analyticsScript = new AnalyticsScript();
Template::addScript($analyticsScript->getStoragePath());
?>
