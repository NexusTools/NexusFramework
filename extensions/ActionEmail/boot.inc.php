<?
if(REQUEST_URI == "/" && array_key_exists("continue", $_GET)
		&& array_key_exists("token", $_GET))
	ActionEmail::__handleToken();
?>
