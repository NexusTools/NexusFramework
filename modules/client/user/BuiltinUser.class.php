<?php
class BuiltinUser extends BasicBuiltinUser {

	protected function setEmailImpl($email) {
		throw new Exception("Email of Built-in Users cannot be set");
	}

	public function setPassword($password) {
		throw new Exception("Builtin Users do not have Passwords");
	}

	public function checkPassword($password) {
		return false;
	}

}
?>
