<?php
class RootUser extends BasicBuiltinUser {

	private static $instance = false;

	public static function instance() {
		return self::$instance === false ? (self::$instance = new RootUser()) : self::$instance;
	}

	protected function __construct() {
		$emailFile = INDEX_PATH."framework.config.rootemail.txt";
		$email = is_file($emailFile) && is_readable($emailFile) ? trim(file_get_contents($emailFile)) : "";
		UserInterface::__construct(0, strlen($email) ? $email : "[unset]", "root", 6);
	}

	protected function setEmailImpl($email) {
		return file_put_contents(INDEX_PATH."framework.config.rootemail.txt", $email) !== false;
	}

	public function setPassword($password) {
		return file_put_contents(INDEX_PATH."framework.config.rootpass.bin", hash("sha512", $password, true)) !== false;
	}

	public function checkPassword($password) {
		$passData = file_get_contents(INDEX_PATH."framework.config.rootpass.bin");
		return $passData && $passData == hash("sha512", $password, true);
	}

}
?>
