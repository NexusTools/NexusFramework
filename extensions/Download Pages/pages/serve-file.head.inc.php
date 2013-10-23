<?php
if (!file_exists(INDEX_PATH.REQUEST_URI))
	Framework::runPage("/errordoc/404");

Template::setTitle("Download File");
?>
