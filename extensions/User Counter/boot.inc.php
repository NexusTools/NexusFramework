<?php
Template::addHeader(array("UserCounter", "tick"));
register_shutdown_function(array("UserCounter", "clean"));
?>
