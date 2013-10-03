<?php
$user = User::checkLogin($_POST['user'], $_POST['pass']);
return $user;
?>
