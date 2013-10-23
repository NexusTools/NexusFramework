<?php
class UserSettings {

	private $uid;

	public function __construct($user) {
		$this->uid = $user->getID();
	}

	public function readSetting($name, $default = null) {

	}

	public function writeSetting($name, $value) {

	}

}
?>
