<?php
$averages = "<span>{{SystemInfo::getHTMLLoad(0)}}&nbsp;&nbsp;&nbsp;";
$averages .= "{{SystemInfo::getHTMLLoad(1)}}&nbsp;&nbsp;&nbsp;";
$averages .= "{{SystemInfo::getHTMLLoad(2)}}</span>";
ControlPanel::registerToolbarWidget("Load: {{SystemInfo::getHTMLLoad(0, true)}}, Mem: {{SystemInfo::getRamUsagePercent(3)}}",
	Array("<b>System Information</b><span>{{SystemInfo::getHostName()}}</span>",
	"----", "Memory: {{SystemInfo::getRamUsage(2)}} of {{SystemInfo::getRamTotal(2)}}",
	"Swap: {{SystemInfo::getSwapUsage(2)}} of {{SystemInfo::getSwapTotal(2)}}",
	"Load Averages$averages"), false, "sysinfo");

function __systemInfo__importScript($module, $event, $arguments) {
	switch ($event) {
	case "Header":
		Template::addScript(dirname(__FILE__).DIRSEP."live-update.js");
		break;
	}
}

Triggers::watchModule("ControlPanel", "__systemInfo__importScript");
?>
