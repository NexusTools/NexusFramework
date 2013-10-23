<?php
class IOException extends Exception {

	const Unknown = 0x00;
	const NotFound = 0x01;
	const ReadAccess = 0x02;
	const WriteAccess = 0x03;
	const ListAccess = 0x04;
	const Corrupt = 0x05;
	const EOF = 0x06;

	private $path;

	public function __construct($path, $type = self::Unknown, $mess = false) {
		if ($mess === false)
			switch ($type) {
			case self::NotFound:
				$mess = "File/Directory Missing";
				break;

			case self::ReadAccess:
				$mess = "Lacking Read Permission";
				break;

			case self::WriteAccess:
				$mess = "Lacking Write Permission";
				break;

			case self::ListAccess:
				$mess = "Lacking List Permission";
				break;

			case self::Corrupt:
				$mess = "Contents Corrupt";
				break;

			case self::EOF:
				$mess = "Unexpected End of File";
				break;

			default:
				$mess = "Unknown Error";
				break;
			}

		Exception::__construct($mess, $type);
		$this->path = $path;
	}

	public function getEffectedPath() {
		return $this->path;
	}

	public function getDetails() {
		return Array("Path" => $this->path);
	}

	public static function throwNotFound($path) {
		throw new IOException($path, self::NotFound);
	}

	public static function throwReadError($path) {
		throw new IOException($path, self::ReadAccess);
	}

	public static function throwWriteError($path) {
		throw new IOException($path, self::WriteAccess);
	}

	public static function throwListError($path) {
		throw new IOException($path, self::ListAccess);
	}

	public static function throwEOFError($path) {
		throw new IOException($path, self::EOF);
	}

}
?>
