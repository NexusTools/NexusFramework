<?php
$user = User::fetch(PageModule::getArgument(1));
if ($user->isValid())
	return "profile";
else
	return false;
?>
