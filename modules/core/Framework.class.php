<?php
class Framework {

	const RawHash = 0;
	const Base64EncodedHash = 1;
	const HexEncodedHash = 2;
	const FilenameSafeHash = 3;
	const URLSafeHash = 4;

	private static $legacyTags = Array("A", "ABBR", "ACRONYM", "ADDRESS", "APPLET", "AREA", "B", "BASE", "BASEFONT", "BDO", "BIG", "BLOCKQUOTE", "BODY", "BR", "BUTTON", "CAPTION", "CENTER", "CITE", "CODE", "COL", "COLGROUP", "DD", "DEL", "DFN", "DIR", "DIV", "DL", "DT", "EM", "FIELDSET", "FONT", "FORM", "FRAME", "FRAMESET", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML", "I", "IFRAME", "IMG", "INPUT", "INS", "ISINDEX", "KBD", "LABEL", "LEGEND", "LI", "LINK", "MAP", "MENU", "META", "NOFRAMES", "NOSCRIPT", "OBJECT", "OL", "OPTGROUP", "OPTION", "P", "PARAM", "PRE", "Q", "S", "SAMP", "SCRIPT", "SELECT", "SMALL", "SPAN", "STRIKE", "STRONG", "STYLE", "SUB", "SUP", "TABLE", "TBODY", "TD", "TEXTAREA", "TFOOT", "TH", "THEAD", "TITLE", "TR", "TT", "U", "UL", "VAR");
	private static $boundResourcePaths = array();
	private static $suppressRedirects = false;
	private static $pageContent = "";
	
	public static function getLegacyTags() {
		return self::$legacyTags;
	}
	
	public static function isLegacyTag($tag) {
		return in_array(strtoupper($tag), self::$legacyTags);
	}

	public static function addResourcePath($key, $path = false) {
		$path = fullpath($path ? $path : $key);
		if (!endsWith($path, "/"))
			$path .= "/";
		if (endsWith($key, "/"))
			$key = substr($key, 0, strlen($key)-1);
		self::$boundResourcePaths[$key] = $path;
	}

	public static function suppressRedirects($on = true) {
		self::$suppressRedirects = $on;
	}

	public static function isHeadRequest() {
		return $_SERVER['REQUEST_METHOD'] == "HEAD";
	}

	public static function pushContent($content) {
		self::$pageContent .= $content;
	}

	public static function runErrorPage($num, $status = false) {
		if ($status)
			header("HTTP/1.1 $num $status");
		self::runPage("errordoc/$num", !$status);
	}

	public static function getConfigFolder($folder) {
		$rfolder = cleanpath(CONFIG_PATH.$folder.DIRSEP);
		if (!is_dir($rfolder) && !mkdir($rfolder, 0777, true))
			throw new Exception("Failed to make config folder `$folder`");
		return $rfolder;
	}

	public static function getTempFolder($folder, $shared = false) {
		$rfolder = cleanpath(($shared ? SHARED_TMP_PATH : TMP_PATH).$folder.DIRSEP);
		if (!is_dir($rfolder) && !mkdir($rfolder, 0777, true))
			throw new Exception("Failed to make temp folder `$folder`");
		return $rfolder;
	}

	public static function redirect($path, $seeOther = false, $rawUri = false) {
		if (self::$suppressRedirects)
			throw new Exception("Redirects Supressed: $path");

		if (defined("INAPI"))
			return;

		if (headers_sent())
			throw new Exception("Cannot Redirect: Headers Already Sent");

		if (!$rawUri && !preg_match("/^\w{2,6}:/", $path))
			$path = BASE_URI.relativepath($path);

		header("location: $path");
		if (is_string($seeOther)) {
			header("HTTP/1.1 303 $seeOther");
			self::runPage("errordoc/303", false);
		} else {
			header("HTTP/1.1 302 Found");
			self::runPage("errordoc/302", false);
		}

		exit();
	}

	public static function fileETag($file) {
		$fs = stat($file);
		return sprintf('%x-%x-%s', $fs['ino'], $fs['size'], base_convert(str_pad($fs['mtime'], 16, "0"), 10, 16));
	}

	public static function testCondition($condition) {
		if (!is_string($condition) || !strlen($condition))
			return true;

		return eval("return $condition;");
	}

	public static function serveData($data, $mimetype = false) {
		if (!$mimetype)
			if (preg_match("/^\s*?</", $data)) {
				if (preg_match("/^\s*?<(html|!doctype)/i", $data))
					$mimetype = "text/html; charset=UTF-8";
				else
					$mimetype = "text/xml; charset=UTF-8";
			} else
				$mimetype = "text/plain; charset=UTF-8";

		header("Content-Type: $mimetype");
		Framework::serveRawData($data, $mimetype);
	}

	public static function serveRawData($data, $mimetype = false) {
		header("X-Content-Type-Options: nosniff");
		OutputFilter::startCompression();
		echo $data;
		exit;
	}

	public static function serveFile($file, $mimetype = false, $realName = false, $expiresAt = false) {
		self::serveFileInternal(fullpath($file), $mimetype, $realName, $expiresAt);
	}

	private static function serveFileInternal($file, $mimetype = false, $realName = false, $expiresAt = false) {
		if (!file_exists($file))
			self::runPage("/errordoc/404");
		if (!is_readable($file))
			self::runPage("/errordoc/403");
		
		if (is_dir($file)) {
			$file = cleanpath($file);
			
			if (startsWith($file, FRAMEWORK_RES_PATH))
				$path = "/res".RES_CONNECTOR.substr($file, strlen(FRAMEWORK_RES_PATH));
			else {
				$path = false;
				if (!startsWith($file, INDEX_PATH)) {
					foreach(self::$boundResourcePaths as $key => $rpath) {
						if(startsWith($file, $rpath)) {
							$path = "/res" . RES_CONNECTOR . $key . "/" . substr($file, strlen($rpath));
							break;
						}
					}
				
					if(!$path)
						self::runPage("/errordoc/404");
				} else
					$path = substr($file, strlen(INDEX_PATH) - 1);
			}
			if (!($handle = opendir($file)))
				self::runPage("/errordoc/500");

			if (is_file($indexPath = $file."index.htm") && is_readable($indexPath))
				self::serveFileInternal($indexPath);
			if (is_file($indexPath = $file."index.html") && is_readable($indexPath))
				self::serveFileInternal($indexPath);

			ExtensionLoader::loadEnabledExtensions();
			if (defined("NO_DIRECTORY_LISTINGS") && !User::isSuperAdmin())
				self::runPage("/errordoc/403");

			header("Content-Type: text/html; charset=UTF-8");
			Triggers::broadcast("FileServer", "ServeDirectory", $path);
			OutputFilter::startCompression();
?><!DOCTYPE html>
<html><head><title>Directory Listing: <?php echo $path; ?></title>
<meta name="robots" content="noindex, nofollow" />
<meta name="description" content="Directory listing for <?php echo $path; ?>" />
<link href="<?php echo BASE_URI; ?>res<?php echo RES_CONNECTOR; ?>dirstyle" rel="stylesheet" type="text/css" /></head>
<body><h1>Directory Listing: <?php echo $path; ?></h1>
<table cellspacing="0" cellpadding="0"><tr><th colspan="2">Filename</th><th>Type</th><th>Size</th></tr>
<?php

			if($path == "/res:") {
				foreach(self::$boundResourcePaths as $key => $rpath) {
					?><tr><td><img src="<?php echo BASE_URI . "res" . RES_CONNECTOR . "icon/folder"; ?>" width="22" height="22" /></td><td><a href="<?php echo BASE_URI; ?>res:<?php
	echo $key;
	?>"><?php echo $key; ?></a></td><td>alias</td><td><?php
				echo StringFormat::formatFilesize(filesize($rpath));
?></td></tr><?php
				}
			}

			chdir($file);
			$files = Array();
			while (false !== ($entry = readdir($handle))) {
				if ($entry == "." || $entry == ".." || !is_readable($entry))
					continue;

				if (is_file($entry)) {
					if (!endsWith($entry, ".php") && !endsWith($entry, "~"))
						array_push($files, $entry);
					continue;
				}

				$path = BASE_URI.REQUEST_URI;
				if (!endsWith($path, RES_CONNECTOR) && !endsWith($path, DIRSEP))
					$path .= DIRSEP;
?><tr><td><img src="<?php echo BASE_URI . "res" . RES_CONNECTOR . "icon/folder"; ?>" width="22" height="22" /></td><td><a href="<?php
	echo cleanpath($path . $entry);
	?>"><?php echo $entry; ?></a></td><td>directory</td><td><?php
				echo StringFormat::formatFilesize(filesize($entry));
?></td></tr><?php
			}

			foreach ($files as $entry) {
?><tr><td><?php
				$size = filesize($entry);
				$mime = self::mimeForFile($entry);
				if (startsWith($mime, "image/")) {
					$icon = BASE_URI."res".RES_CONNECTOR."icon/image";
					try {
						if ($size < 5242880 && class_exists("ModifiedImage"))
							$icon = ModifiedImage::scaledTransparentURI($entry, 22, 22, ModifiedImage::KeepAspectRatio);
					} catch (Exception $e) {
					}
				} else {
					if ($size > 0) {
						if (startsWith($mime, "video/"))
							$icon = BASE_URI."res".RES_CONNECTOR."icon/video";
						else
							if (startsWith($mime, "audio/"))
								$icon = BASE_URI."res".RES_CONNECTOR."icon/audio";
							else
								if (preg_match("#^(text|application)/.*(xml|html?).*$#i", $mime))
									$icon = BASE_URI."res".RES_CONNECTOR."icon/xml";
								else
									if (preg_match("#^(application)/.*(pdf).*$#i", $mime))
										$icon = BASE_URI."res".RES_CONNECTOR."icon/pdf";
									else
										if (preg_match("/compressed|zip|archive/", $mime))
											$icon = BASE_URI."res".RES_CONNECTOR."icon/archive";
										else
											if (startsWith($mime, "text/"))
												$icon = BASE_URI."res".RES_CONNECTOR."icon/text";
											else
												$icon = BASE_URI."res".RES_CONNECTOR."icon/unknown";
					} else
						$icon = BASE_URI."res".RES_CONNECTOR."icon/empty";
				}

				$path = BASE_URI.REQUEST_URI;
				if (!endsWith($path, RES_CONNECTOR) && !endsWith($path, DIRSEP))
					$path .= DIRSEP;
?><img src="<?php echo $icon; ?>" width="22" height="22" /></td><td><a href="<?php
	echo cleanpath($path . $entry);
	?>"><?php echo $entry; ?></a></td><td><?php
				echo $mime;
?></td><td><?php
				echo StringFormat::formatFilesize(filesize($entry));
?></td></tr><?php
			}

			closedir($handle);
?></table><hr /><sup>Generated by NexusFramework</sup></body></html><?php
			exit;
		}
		if (endsWith($file, ".php"))
			self::runPage("/errordoc/403");

		if (!($size = filesize($file)))
			self::runPage("/errordoc/204");

		Triggers::broadcast("FileServer", "ServeFile", $file);
		$reader = fopen($file, "rb");
		if (!$reader || !flock($reader, LOCK_SH))
			self::runPage("/errordoc/500");

		$etag = self::fileETag($file);
		$modtime = self::formatGMTDate(filemtime($file));
		if (!is_string($mimetype) || !strlen($mimetype = trim($mimetype))) {
			$mimetype = self::mimeForFile($file);

			if ($mimetype && !startsWith($mimetype, "text/") && !startsWith($mimetype, "application/") && !endsWith($mimetype, "+xml"))
				header("X-Content-Type-Options: nosniff");
		} else
			header("X-Content-Type-Options: nosniff");
		if (headers_sent($header_file, $header_line)) {
			OutputFilter::startRawOutput();
			die("Headers Already Sent by: $header_file:$header_line");
		}

		header_remove("Cache-Control");
		header_remove("Pragma");

		if ($size > 1048510)
			header('Connection: close');
		header("Content-Type: $mimetype");
		header("Last-Modified: $modtime");
		header("ETag: $etag");

		if (is_string($realName)) {
			$safeFilename = urlencode(is_string($realName) ? $realName : basename($file));

			if (!startsWith($mimetype, "text/") && !startsWith($mimetype, "image/") && !startsWith($mimetype, "video/") && !startsWith($mimetype, "audio/"))
				header("Content-Disposition: attachment; filename=\"$safeFilename\"");
			else
				header("Content-Disposition: inline; filename=\"$safeFilename\"");

			header("X-Filename: $safeFilename");
		}

		if (!$expiresAt) // Default to expiring after between 2 and 15 minutes to try and balance update checking
			$expiresAt = time() + rand(strtotime("+1 week", 0), strtotime("+2 weeks", 0));
		header("Expires: ".self::formatGMTDate($expiresAt));
		header("X-Process-Time: ".(microtime(true) - LOADER_START_TIME) * 1000);

		$headers = getallheaders();
		if ((isset($headers['If-None-Match']) && $headers['If-None-Match'] == $etag) || (isset($headers['If-Modified-Since']) && $headers['If-Modified-Since'] == $modtime)) {
			header("HTTP/1.1 304 Not Modified");
			exit;
		}
		if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/',
			$_SERVER['HTTP_RANGE'], $range)) {
			$range = Array(intval($range[1]), intval($range[2]));
			if ($range[1] < 1)
				$range[1] = $size - 1;

			$length = $range[1] - $range[0];

			if ($length > 0 && $length < $size) {
				header('Accept-Ranges: bytes');
				header('HTTP/1.1 206 Partial Content');
				header("Content-Range: bytes $range[0]-$range[1]/$size");
				$length++;
				header("Content-Length: $length");

				if (!self::isHeadRequest()) {
					OutputFilter::resetToNative(false);

					if ($range[0])
						fseek($reader, $range[0]);

					$left = $length;
					while ($data = fread($reader, $left > 1024 ? 1024 : $left)) {
						$left -= strlen($data);
						echo $data;
					}
				}

				exit;
			}
		}

		if (!self::isHeadRequest()) {
			if (startsWith($mimetype, "text/") && $size < 1048510)
				OutputFilter::startCompression();
			else {
				header("Content-Length: $size");
				OutputFilter::resetToNative(false);
			}

			while ($data = fread($reader, 1024))
				echo $data;
		}

		exit;
	}

	public static function formatGMTDate($timestamp = false) {
		if (!$timestamp)
			$timestamp = time();
		return gmdate("D, d M Y H:i:s \G\M\T", $timestamp);
	}

	private static function processTagClass($matches) {
		$matches[2] = trim($matches[2]);
		$matches[1] = strtolower(preg_replace("/[^\w]/", "_", $matches[1]));
		if (!strlen($matches[2]))
			return "<div class=\"$matches[1]\">";

		if (preg_match("/class=(\"[\w\d\s\-\_]+\"|'[\w\d\s\-\_]+')/i", $matches[2], $newMatches)) {
			$matches[2] = trim(preg_replace("/class=(\"[\w\d\s\-\_]+\"|'[\w\d\s\-\_]+')/i", "", $matches[2]));
			$matches[1] = trim($matches[1]." ".substr($newMatches[1], 1, strlen($newMatches[1]) - 2));
		}

		return "<div class=\"$matches[1]\" $matches[2]>";
	}

	public static function serveMediaFile($path) {
		$path = fullpath($path);
		if (!startsWith($path, MEDIA_PATH))
			self::runPage("/errordoc/403");
		self::serveFileInternal($path);
	}
	
	public static function serveReference($id, $onlyShared =false) {
		if (!$onlyShared && is_file($refFile = TMP_PATH."ref".DIRSEP.$id) && $data = unserialize(file_get_contents($refFile)))
			self::serveFile($data['path'], $data['mime'], isset($data['filename']) ? $data['filename'] : false);
		else
			if (is_file($refFile = SHARED_TMP_PATH."ref".DIRSEP.$id) && $data = unserialize(file_get_contents($refFile)))
				self::serveFile($data['path'], $data['mime'], isset($data['filename']) ? $data['filename'] : false);
			else
				self::runPage("/errordoc/404");
	}

	public static function serveResource($res, $onlyShared = false) {
		if (startsWith($res, "ref/"))
			self::serveReference(substr($res, 4));
		else
			if (startsWith($res, "badref/") && is_file($refFile = TMP_PATH."badref".DIRSEP.substr($res, 7)) && $data = unserialize(file_get_contents($refFile))) {
				$expiresAt = time() + rand(strtotime("+1 month", 0), strtotime("+2 month", 0));
				header("Expires: ".self::formatGMTDate($expiresAt));
				OutputFilter::startCompression();

				echo "<html><head><title>Invalid Resource</title></head><body><h1>Invalid Resource</h1><p>The resource `$data[path]` is missing.";
				if (class_exists("User", false) && User::isStaff()) {
					echo "<h2>Backtrace</h2><pre>";
					print_r($data['backtrace']);
					echo "</pre>";
				}
				die("</p></body></html>");
			} else
				if (startsWith($res, "icon/"))
					self::serveFileInternal(FRAMEWORK_RES_PATH."icons".DIRSEP.(array_key_exists("s", $_GET) ? $_GET['s'] : 22).DIRSEP.substr($res, 5).".png", "image/png");

		switch ($res) {
		case "internal-error":
			$expiresAt = time() + rand(strtotime("+1 month", 0), strtotime("+2 month", 0));
			header("Expires: ".self::formatGMTDate($expiresAt));
			OutputFilter::startCompression();
			echo "<html><head><title>Internal Error Occured</title></head><body><h1>Internal Error Occured</h1><p>An internal error occured while processing your request,<br />This error has been logged and we are working to fix it.<br />Sorry for any inconvenience.</p><pre style='font-size: 70%; opacity: 0.7'>";
			echo $_GET['__errMess'];
			echo "</pre></body></html>";
			die;

		case "lgpl":
			self::serveFile(FRAMEWORK_RES_PATH."LGPL3.0.html");

		case "script":
			$expiresAt = time() + rand(strtotime("+1 month", 0), strtotime("+2 month", 0));
			header("Expires: ".self::formatGMTDate($expiresAt));
			OutputFilter::startCompression();
			$scmpr = new ScriptCompressor(true);
			$path = FRAMEWORK_RES_PATH."javascript".DIRSEP;

			$scmpr->addScript($path."prototype.js");
			foreach (glob($path."framework".DIRSEP."*.js") as $script) {
				$scmpr->addScript($script);
			}
			$scmpr->dumpAsResponse();
			exit;

		case "dirstyle":
			$expiresAt = time() + rand(strtotime("+1 month", 0), strtotime("+2 month", 0));
			header("Expires: ".self::formatGMTDate($expiresAt));
			OutputFilter::startCompression();
			$style = new CompressedStyle(FRAMEWORK_RES_PATH."stylesheets".DIRSEP."dirlisting.css");
			$style->dumpAsResponse();
			exit;

		case "lgpl-logo":
			self::serveFileInternal(FRAMEWORK_RES_PATH."images".DIRSEP."lgplv3.png", "image/png");

		case "style":
			$expiresAt = time() + rand(strtotime("+1 month", 0), strtotime("+2 month", 0));
			header("Expires: ".self::formatGMTDate($expiresAt));
			OutputFilter::startCompression();
			$style = new CompressedStyle(FRAMEWORK_RES_PATH."stylesheets".DIRSEP."widgets.css");
			$style->dumpAsResponse();
			exit;

		case "information":
			header("Content-Type: text/plain");
			OutputFilter::startCompression();
			echo "Version: ".FRAMEWORK_VERSION;
			echo "\nInstall Path: ".FRAMEWORK_PATH;
			echo "\nBase URL: ".BASE_URL;
			exit;

		}


		if(startsWith($res, "user-themes/"))
			self::serveFileInternal(INDEX_PATH . "themes" . DIRSEP . substr($res, 12));
		
		ExtensionLoader::loadEnabledExtensions();
		foreach (self::$boundResourcePaths as $key => $path) {
			if (startsWith($res, "$key/") || $key == $res)
				self::serveFileInternal($path.substr($res, strlen($key) + 1));
		}

		self::serveFileInternal(FRAMEWORK_RES_PATH.$res);
	}

	public static function run($requestURI, $basePath) {
		global $__framework_activePath;
		Profiler::start("Framework");
		$__framework_activePath = $basePath;
		chdir($basePath);

		$requestURI = relativepath($requestURI);
		if ($requestURI == "robots.txt") {
			if (is_file("robots.txt"))
				self::serveFileInternal("robots.txt", "text/plain");
			else
				if (defined("NO_ROBOTS")) {
					$robotContent = "# Automatically Generated
User-agent: *
Disallow: "
						.BASE_URI;
					header("Content-Type: text/plain");
					OutputFilter::startCompression();

					echo $robotContent;
					exit;
				}
		}

		if (file_exists("upgrade-message")) {
			header("Content-Type: text/plain");
			OutputFilter::startCompression();
			echo file_get_contents("upgrade-message");
			die;
		}

		if (!count($_POST) && !count($_GET)) {
			$clean = "/$requestURI";
			if ($clean != REQUEST_URI)
				self::redirect($clean);
			else
				unset($clean);
		} else if(isset($_GET['__refID']))
			self::serveReference($_GET['__refID']);

		if (startsWith($requestURI, "media")) {
			if (file_exists($requestURI))
				self::serveFileInternal(INDEX_PATH.$requestURI);
			else
				self::runPage("/errordoc/404");
		}

		if (startsWith($requestURI, "res".RES_CONNECTOR))
			self::serveResource(substr($requestURI, 4));
		else
			if ($requestURI == "" && isset($_GET['api'])) {
				if (DEBUG_MODE)
					Profiler::finish("Framework");

				if (self::isHeadRequest())
					exit;
				API::run();
			}

		if (file_exists("$requestURI.php") && REQUEST_URI != "/index" && REQUEST_URI != "/framework.config") {
			@chdir(dirname($requestURI));
			OutputFilter::resetToNative(false);
			require("$requestURI.php");
			exit;
		} else
			if (is_dir($requestURI) && file_exists("$requestURI/index.php")) {
				@chdir($requestURI);
				OutputFilter::resetToNative(false);
				require("index.php");
				exit;
			}

		if (DEBUG_MODE)
			Profiler::finish("Framework");
		self::runPage($requestURI);
	}

	public static function isLegacyOS() {
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	public static function uniqueHash($string = false, $encodeOptions = self::FilenameSafeHash, $md5 = false) {
		if (!$string) {
			if (function_exists("openssl_random_pseudo_bytes"))
				$string = openssl_random_pseudo_bytes(20);
			else
				if (function_exists("mt_rand"))
					$string = mt_rand();
				else
					$string = rand();
		} else
			if (is_array($string))
				$string = json_encode($string);
			else
				if (is_object($string))
					$string = $string->toString();

		if ($md5)
			$rawHash = md5($string, true);
		else
			$rawHash = crc32($string);
		switch ($encodeOptions) {
		case self::Base64EncodedHash:
			return base64_encode($rawHash);

		case self::HexEncodedHash:
			return dechex($rawHash);

		case self::FilenameSafeHash:
			return str_replace("/", "_", base64_encode($rawHash));
			break;

		case self::URLSafeHash:
			return rawurlencode(base64_encode($rawHash));
			break;

		default:
			return $rawHash;
		}
	}

	public static function fileHash($file) {
		return self::uniqueHash(stat($file));
	}

	public static function mimeForFile($path) {
		return FileMime::forFile($path);
	}
	
	public static function getBoundURI($rawPath) {
		return self::getBoundPath($rawPath, true);
	}
	
	public static function getBoundURL($rawPath) {
		return self::getBoundPath($rawPath, false);
	}
	
	private static function getBoundPath($rawPath, $relative) {
		if (is_file($path = fullpath($rawPath))) {
			if (startsWith($path, MEDIA_PATH))
				return ($relative ? MEDIA_URI : MEDIA_URL).substr($path, strlen(MEDIA_PATH));

			if (startsWith($path, FRAMEWORK_RES_PATH))
				return ($relative ? BASE_URI : BASE_URL)."res".RES_CONNECTOR.substr($path, strlen(FRAMEWORK_RES_PATH));
			
			$themesPath = INDEX_PATH . "themes"  . DIRSEP;
			if(startsWith($path, $themesPath))
				return ($relative ? BASE_URI : BASE_URL)."res".RES_CONNECTOR."user-themes/".substr($path, strlen($themesPath));
			
			foreach (self::$boundResourcePaths as $key => $boundPath) {
				if (startsWith($path, $boundPath))
					return ($relative ? BASE_URI : BASE_URL)."res".RES_CONNECTOR.$key."/".relativepath(substr($path, strlen($boundPath)));
			}
			
		}
			
		return false;
	}
	
	public static function getReferenceID($rawPath, $mime_type =false, $realFilename =false, $shared =false) {
		if (!is_file($path = fullpath($rawPath))) {
			if (!is_dir(($refPath = ($shared ? SHARED_TMP_PATH : TMP_PATH)."badref".DIRSEP)))
				mkdir($refPath);
			$id = self::uniqueHash($rawPath);
			if (!is_file(($ref_file = "$refPath$id")))
				file_put_contents($ref_file, serialize(Array("path" => $rawPath, "backtrace" => debug_backtrace(0))));
			return ($relative ? BASE_URI : BASE_URL)."res".RES_CONNECTOR."badref/".urlencode($id);
		}
		
		if ($shared && startsWith($rawPath, TMP_PATH))
			$shared = false;

		if (!is_dir(($refPath = ($shared ? SHARED_TMP_PATH : TMP_PATH)."ref".DIRSEP)))
			mkdir($refPath);

		$id = self::uniqueHash($path);
		if (!is_file(($ref_file = "$refPath$id"))) {
			if (!is_string($mime_type))
				$mime_type = self::mimeForFile($path);
			if (is_string($realFilename))
				file_put_contents($ref_file, serialize(Array("path" => $path, "mime" => $mime_type, "filename" => $realFilename)));
			else
				file_put_contents($ref_file, serialize(Array("path" => $path, "mime" => $mime_type)));
		}
		
		return $id;
	}

	private static function getFileReference($rawPath, $mime_type, $realFilename, $shared, $relative) {
		if($boundPath = self::getBoundPath($rawPath, $relative))
			return $boundPath;
			
		$id = self::getReferenceID($rawPath, $mime_type, $realFilename, $shared);

		return ($shared ? ($relative ? SHARED_RESOURCE_URI : SHARED_RESOURCE_URL) : (($relative ? BASE_URI : BASE_URL)."res".RES_CONNECTOR))."ref/".urlencode($id);
	}

	/*
		
	 Framework::getReferenceURI()
			
	 @returns [string] Reference URI for File or Resource
			
	 Return the Reference for a File or Resource,
	 Securing the Backend by Blocking Direct Access.
		
	 */
	public static function getReferenceURI($rawPath, $mime_type = false, $realFilename = false, $shared = true) {
		return self::getFileReference($rawPath, $mime_type, $realFilename, $shared, true);
	}

	public static function getReferenceURL($rawPath, $mime_type = false, $realFilename = false, $shared = true) {
		return self::getFileReference($rawPath, $mime_type, $realFilename, $shared, false);
	}

	public static function output($code) {
		return $code;
	}
	
	public static function fixModernTags($matches) {
		if(self::isLegacyTag($matches[1]) ||
				startsWith($matches[0], "<!"))
			return $matches[0];
		
		if(startsWith($matches[0], "</")) {
			return "</div>";
		}
			
		if(preg_match("/class=['\"](.+)['\"]/", $matches[2], $cMatch, PREG_OFFSET_CAPTURE)) {
			$newClass = "";
			if($cMatch[0][1] > 0)
				$newClass = substr($matches[2], 0, $cMatch[0][1]);
			$newClass .= "class=\"" . StringFormat::idForDisplay($matches[1]) . "-el " .$cMatch[1][0]. "\"";
			$newClass .= substr($matches[2], $cMatch[0][1] + strlen($cMatch[0][0]));
			$matches[2] = $newClass;
		} else
			$matches[2] = " class=\"" . StringFormat::idForDisplay($matches[1]) . "-el\"";
		return "<div" . $matches[2] . ">";
	}

	public static function runPage($path, $changeStatus = true) {
		if (array_key_exists("__nocache__", $_GET))
			$_SESSION['cache-filter'] = $_GET['__nocache__'];
		else
			if (array_key_exists("cache-filter", $_SESSION))
				unset($_SESSION['cache-filter']);

		ExtensionLoader::loadEnabledExtensions();
		Database::commitAll(); // Dump changes first

		if (NOACCESS_MODE && ($baseDomain = preg_replace("/.+\.(\w+\.\w+)/", "$1", DOMAIN)))
			redirectDomain($baseDomain);

		$module = new PageModule($path);
		$module->initialize($changeStatus);
		$module->getTheme()->initialize();
		$outputCapture = new OutputCapture();

		if (DEBUG_MODE) {
			if (isset($_GET['dumpstate'])) {
				Framework::dumpState();
				exit();
			}

			Profiler::start("Template");
		}

		if (self::isHeadRequest())
			exit;

		Template::writeHeader();
		echo "<framework:theme>";
		$module->getTheme()->runHeader();
		echo "<framework:page>";
		Triggers::broadcast("Template", "ServePage", "header");
		$module->run();
		Triggers::broadcast("Template", "ServePage", "footer");
		echo "</framework:page>";
		$module->getTheme()->runFooter();
		echo "</framework:theme>";
		Template::writeFooter();
		
		if(LEGACY_BROWSER) {
			$data = $outputCapture->finish();
			$data = preg_replace_callback("/<\\/?([\\w:]+)(\\s[^>]+)?>/", "Framework::fixModernTags", $data);
			
			self::serveData($data);
		} else
			$outputCapture->serve();
	}

	public static function splitPath($path) {
		return preg_split('/\//', $path, -1, PREG_SPLIT_NO_EMPTY);
	}

	public static function dumpState() {
		Profiler::start("StateDump");
		OutputFilter::resetToNative(false);
		echo "<html><head><title>Framework State Debugger</title></head><body>";

		DebugDump::dump("-ClassLoader");
		User::initAllBackends();
		foreach (ClassLoader::getLoadedClasses() as $name)
			if ($name != "Profiler")
				DebugDump::dump("-$name");

		DebugDump::dump(get_loaded_extensions(), "PHP Extensions");
		DebugDump::dump(get_loaded_extensions(true), "Zend Extensions");
		DebugDump::dump(get_defined_constants(true), "Constants");
		DebugDump::dump($_SESSION, "Session");
		Profiler::finish("StateDump");
		DebugDump::dump(Profiler::getTimers(), "Profiler");

		echo "<br /><br /><span style=\"font-size: 10px;\">";
		echo "Took ".number_format((microtime(true) - LOADER_START_TIME) * 1000, 2)."ms to Generate.";
		echo "</span></body></html>";
	}

}
?>
