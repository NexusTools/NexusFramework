<?php
preg_match("/^(\d+\.\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)/", file_get_contents("/proc/loadavg"), $loadavg);
array_shift($loadavg);

$handle = popen('free', 'r');
fgets($handle); // Skip Headers
preg_match("/^Mem\:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", fgets($handle), $memory);
fgets($handle); // Skip Other
preg_match("/^Swap\:\s+(\d+)\s+(\d+)\s+(\d+)/", fgets($handle), $swap);
pclose($handle);

return Array(
	"loadavg" => $loadavg,
	"procinfo" => Array(
		"running" => system("ps o state ax | grep R | wc -l"),
		"sleeping" => system("ps o state ax | grep S | wc -l"),
		"zombie" => system("ps o state ax | grep Z | wc -l")
	),
	"memory" => Array("system" => Array(
		"total" => $memory[1],
		"used" => $memory[2] - $memory[6],
		"free" => $memory[3],
		"shared" => $memory[4],
		"buffers" => $memory[5],
		"cache" => $memory[6]),
		"swap" => Array(
			"total" => $swap[1],
			"used" => $swap[2],
			"free" => $swap[3]
		)));
?>
