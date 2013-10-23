<?php

if (array_key_exists("token", $_GET))
	ActionEmail::__handleToken();
else
	Framework::redirect("/");
?>
