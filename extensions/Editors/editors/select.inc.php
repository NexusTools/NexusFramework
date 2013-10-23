<?php
switch ($mode) {
case EditCore::RENDER:
	echo "<select class=\"text\" name=\"$name\" value=\"";
	echo htmlspecialchars($value);
	echo "\" style=\"width: 350px\">";
	if (isset($meta['options']))
		$options = $meta['options'];
	else
		$options = call_user_func($meta['retreiver']);

	if (!isset($meta['force-raw']) && isset($options['database']))
		$options = Database::getInstance($options['database'])->selectFields($options['table'], isset($options['field']) ? $options['field'] : "name");

	foreach ($options as $ovalue => $display) {
		echo "<option value=\"";
		echo htmlspecialchars($ovalue);
		echo "\" ";
		if ($ovalue == $value)
			echo " selected";
		echo ">$display</option>";
	}
	echo "</select>";
	break;
}
?>
