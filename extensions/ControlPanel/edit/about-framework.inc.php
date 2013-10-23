<center><h1>Nexus PHP Framework Information</h1><table>
<?php
$data = Array();
$data['Framework Version'] = FRAMEWORK_VERSION;
$data['Server Software'] = $_SERVER['SERVER_SOFTWARE'];
$data['PHP Version'] = phpversion();

$alt = false;
foreach ($data as $key => $value) {
	echo "<tr";
	if ($alt) {
		echo " class=\"alt\"";
		$alt = false;
	} else
		$alt = true;
	echo "><td>";
	echo htmlentities($key);
	echo "</td><td>";
	echo htmlentities($value);
	echo "</td></tr>";
}
?></table></center>
