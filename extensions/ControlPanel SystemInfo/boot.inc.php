<?php
$averages = "<span>{{SystemInfo::getHTMLLoad(0)}}&nbsp;&nbsp;&nbsp;";
$averages .= "{{SystemInfo::getHTMLLoad(1)}}&nbsp;&nbsp;&nbsp;";
$averages .= "{{SystemInfo::getHTMLLoad(2)}}</span>";
ControlPanel::registerToolbarWidget(
		"Load: {{SystemInfo::getHTMLLoad(0, true)}}, Mem Usage: {{SystemInfo::getRamUsagePercent()}}",
		Array("<b>System Information</b><span>{{SystemInfo::getHostName()}}</span>",
		"----", "Memory: {{SystemInfo::getRamUsage(2)}} of {{SystemInfo::getRamTotal(2)}}",
		"Swap: {{SystemInfo::getSwapUsage(2)}} of {{SystemInfo::getSwapTotal(2)}}",
		"Load Averages$averages"), "Users/Online");
?>
