<?php
class ScriptCompressor extends CachedFileSet {

	public function getPrefix() {
		$prefix = "";
		if (LEGACY_BROWSER)
			$prefix .= "legacy-";
		if (DEBUG_MODE)
			$prefix .= "debug-";
		return $prefix."script";
	}

	public function getMimeType() {
		return "text/javascript";
	}

	public static function compress($code) {
		return trim(JSMin::minify($code));
	}

	public static function processContent($code, $compress = true, $interpolate = true) {
		$volatile = preg_match("/^@volatile\s/i", $code);
		if ($volatile)
			$code = substr($code, 10);
		else
			if (!DEBUG_MODE && $compress)
				$code = self::compress($code);

		/* disabled until I can fix it
		 if(false && LEGACY_BROWSER) {
		 foreach(Framework::legacyTags() as $tag){
		 $cleanTag = preg_replace("/[^\\w]/", "_", $tag);
		 $tag = preg_quote(strtoupper($tag));
		 $code = preg_replace("/\"$tag\"/i", "\"div.$cleanTag\"", $code);
		 $code = preg_replace("/'$tag'/i", "'div.$cleanTag'", $code);
		 }
		 $code = "/* This file has been automatically modified to support Legacy Browsers \n" . $code;
		 }
		 */

		if (!$volatile && $interpolate)
			$code = interpolate($code);

		return $code;
	}

	public function processFile($path) {
		$code = file_get_contents($path);
		if (startsWith($path, FRAMEWORK_PATH."resources".DIRSEP))
			$code = self::processContent($code, true, false);
		else
			$code = self::processContent($code);

		if (DEBUG_MODE)
			return "/* $path */\n".$code;
		else
			return $code;
	}

	public function addScript($path) {
		$this->addFile($path);
	}

}
?>
