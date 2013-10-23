<?php
switch ($mode) {
case EditCore::RENDER:
	echo "<input class=\"text\" name=\"$name\" value=\"";
	echo htmlspecialchars($value);
	echo "\" style=\"width: 350px\" />";
	break;

case EditCore::VALIDATE:
	if (!$value || !strlen($value))
		return "Required";

	if (preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",
		$value)) {
		list($username, $domain) = explode('@', $value);
		if (!checkdnsrr($domain, 'MX')) {
			return "Invalid Domain";
		}
		return true;
	}
	return "Invalid Syntax";

}
?>
