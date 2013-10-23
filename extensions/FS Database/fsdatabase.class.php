<?php
class FilesystemDatabase extends BasicEmuDatabase {

	private $filepath;
	private $basepath;
	private $childpath;

	protected function isShared() {
		return false;
	}

	public function __construct($basepath, $childpath = "") {
		$this->basepath = fullpath($basepath);
		if (!endsWith($this->basepath, "/"))
			$this->basepath .= "/";
		$this->childpath = relativepath($childpath);
		$this->filepath = $this->basepath.$this->childpath;
		if (!endsWith($this->filepath, "/"))
			$this->filepath .= "/";
	}

	public function getFilepath() {
		return $this->filepath;
	}

	public function getEntries() {
		$fsdb = new CachedFilesystemDatabase($this->filepath);
		$entries = $fsdb->getData();
		foreach ($entries as & $entry)
			$entry['uri'] = substr($entry['path'], strlen($this->basepath));
		return $entries;
	}

}

class CachedFilesystemDatabase extends CachedFile {

	protected function isShared() {
		return true;
	}

	public function __construct($path) {
		parent::__construct($path);
	}

	public function getMimeType() {
		return "inode/directory";
	}

	public function getPrefix() {
		return "fs-database";
	}

	private function pushFile($fname, $path, &$array, $mime = false) {
		if (startsWith($fname, ".") || endsWith($fname, "~"))
			return;

		if (is_readable($path)) {
			$stat = stat($path);
			$info = Array("rowid" => $stat['ino'] ? $stat['ino'] : crc32($fname));
			$info['path'] = $path;
			$info['size'] = $stat['size'];
			$info['ctime'] = $stat['ctime'];
			$info['atime'] = $stat['atime'];
			$info['mtime'] = $stat['mtime'];
			$info['mode'] = $stat['mode'];
			if (endsWith($fname, ".sqlite") && is_file($path))
				$info['mime'] = "application/x-sqlite";
			else
				$info['mime'] = $mime ? $mime : mime_content_type($path);
			if (is_link($path)) {
				$info['target'] = readlink($path);
				if ($info['mime'] == "directory")
					$info['target'] .= "/";
				$info['target'] = shortpath($info['target']);
			} else
				$info['target'] = false;
			$info['name'] = $fname;
		} else {
			$info = Array("rowid" => crc32($fname));
			$info['path'] = $path;
			$info['size'] = 0;
			$info['ctime'] = 0;
			$info['atime'] = 0;
			$info['mtime'] = 0;
			$info['mode'] = 0;
			$info['mime'] = "not-readable";
			$info['name'] = $fname;
			$info['target'] = false;
		}
		array_push($array, $info);
	}

	public function update() {
		return $this->getEntries();
	}

	public function getEntries() {
		$entries = Array();

		$dir = opendir($this->getFilepath());
		while (($fname = readdir($dir)) !== false) {
			if ($fname == "." || $fname == ".." || !is_dir($file = $this->getFilepath().$fname))
				continue;

			$this->pushFile($fname, $file, $entries);
		}
		closedir($dir);
		$dir = opendir($this->getFilepath());
		while (($fname = readdir($dir)) !== false) {
			$file = $this->getFilepath().$fname;
			if (is_dir($file))
				continue;

			$this->pushFile($fname, $file, $entries);
		}
		closedir($dir);
		return $entries;
	}

}
?>
