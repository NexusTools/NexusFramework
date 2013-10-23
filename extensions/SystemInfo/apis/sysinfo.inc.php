<?php $mem = SystemInfo::getMemoryInfo();
return Array(
	"loadavg" => SystemInfo::getLoadAverages(),
	"procinfo" => Array(
		"running" => system("ps o state ax | grep R | wc -l"),
		"sleeping" => system("ps o state ax | grep S | wc -l"),
		"zombie" => system("ps o state ax | grep Z | wc -l")
	),
	"memory" => Array("system" => Array(
		"total" => $mem[0][1],
		"used" => $mem[0][2] - $mem[0][6],
		"free" => $mem[0][3],
		"shared" => $mem[0][4],
		"buffers" => $mem[0][5],
		"cache" => $mem[0][6]),
		"swap" => Array(
			"total" => $mem[1][1],
			"used" => $mem[1][2],
			"free" => $mem[1][3]
		)));
?>
