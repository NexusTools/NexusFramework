<?php
try {
	$data = ControlPanel::run(PageModule::getArgument(1), PageModule::getArgument(2));
} catch (Exception $e) {
	$data = Array("breadcrumb" => Array("Exception Thrown"), "html" => $e->getMessage());
}
?><breadcrumb><?php
$set = false;
foreach ($data['breadcrumb'] as $breadcrumb) {
	if ($set)
		echo "   »   ";
	else
		$set = true;
	if (is_array($breadcrumb))
		echo "<item onclick=\"".htmlentities($breadcrumb['action'])."\">$breadcrumb[title]</item>";
	else
		echo "<item>$breadcrumb</item>";
}

if ($data['tools']) {
	foreach ($data['tools'] as $tool)
		echo "&nbsp;&nbsp;<img onclick=\"".$tool['action']."\" src='".$tool['icon']."' />";
}
?></breadcrumb><content><?php
echo $data['html'];
?></content>
