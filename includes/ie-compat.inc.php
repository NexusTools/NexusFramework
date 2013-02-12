<?php
if(isset($_SERVER['HTTP_USER_AGENT']) &&
		strpos("MSIE", $_SERVER['HTTP_USER_AGENT']) !== false)
	header("X-UA-Compatibility: IE=IE9,chrome=1");
?>
