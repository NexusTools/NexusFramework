<?php
class EditCore {

	const RENDER = 0x1;
	const VALIDATE = 0x2;
	const DECODE = 0x3;
	const ENCODE = 0x4;

	private static $editors = Array();

	public static function render($type, $name, $value = null, $meta = Array("required" => 1)) {
		$ret = self::run($type, self::RENDER, $name, $value, $meta);
	}

	public static function validate($type, $name, $value = null, $meta = Array("required" => 1)) {
		return self::run($type, self::VALIDATE, $name, $value, $meta);
	}

	public static function decode($type, $value, $meta = Array()) {
		$val = self::run($type, self::DECODE, null, $value, $meta);
		if ($val !== 1)
			return $val;
		return $value;
	}

	public static function encode($type, $value, $meta = Array()) {
		$val = self::run($type, self::ENCODE, null, $value, $meta);
		if (is_string($val) && strlen($val) > 1)
			return $val;
		return $value;
	}

	public static function run($type, $mode, $name, $value = null, $meta = Array("required" => 1)) {
		if ($value === null)
			$value = isset($_POST[$name]) ? $_POST[$name] : false;
		if (isset(self::$editors[$type]))
			return include(self::$editors[$type]);
		else
			echo "No Editor Registered for `$type`";
	}

	public static function registerEditor($type, $script) {
		self::$editors[$type] = fullpath($script);
	}

	public static function getRegisteredEditors() {
		return array_keys(self::$editors);
	}

}

EditCore::registerEditor("line", "editors/line.inc.php");
EditCore::registerEditor("title", "editors/title.inc.php");
EditCore::registerEditor("text", "editors/text.inc.php");
EditCore::registerEditor("condition", "editors/condition.inc.php");
EditCore::registerEditor("yesno", "editors/yesno.inc.php");
EditCore::registerEditor("select", "editors/select.inc.php");
EditCore::registerEditor("email", "editors/email.inc.php");
EditCore::registerEditor("password", "editors/password.inc.php");
EditCore::registerEditor("html", "editors/html.inc.php");
?>
