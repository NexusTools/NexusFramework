<?php
class StyleCompressor extends CachedFileSet {

	public function getPrefix(){
	    $prefix = "";
	    if(LEGACY_BROWSER)
	        $prefix .= "legacy-";
	    if(DEBUG_MODE)
	        $prefix .= "debug-";
		return $prefix . "style";
	}
	
	public function getMimeType(){
		return "text/css";
	}
	
	public static function compress($css){
		$css = preg_replace( '#\s+#', ' ', $css );
		$css = preg_replace( '#/\*.*?\*/#s', '', $css );
		
		$css = str_replace( '; ', ';', $css );
		$css = str_replace( ': ', ':', $css );
		$css = str_replace( ' {', '{', $css );
		$css = str_replace( '{ ', '{', $css );
		$css = str_replace( ', ', ',', $css );
		$css = str_replace( '} ', '}', $css );
		$css = str_replace( ';}', '}', $css );
		$css = str_replace( '; -', ';-', $css );
		$css = trim($css);
		return $css;
	}
	
	public static function processTagClass($matches){
	    return $matches[1] . "div." . strtolower(preg_replace("/(" . preg_quote("\:") . "|[^\w])/", "_", $matches[2]));
	}
	
	public static function rgbToHex($matches){
	    return "black";
	}
	
	public static function processContent($css, $compress=true, $interpolate=true){
	    if(startsWith($css, "@volatile\n"))
			$css = substr($css, 10);
		else if(!DEBUG_MODE && $compress)
		    $css = self::compress($css);
		
		if(LEGACY_BROWSER) {
		    $tagSwitch = "";
	        foreach(Framework::legacyTags() as $tag){
	            if(strlen($tagSwitch))
	                $tagSwitch .= "|";
	            $tagSwitch .= preg_quote(preg_replace("/:/", "\\:", $tag));
	        }
		    
		    $css = preg_replace_callback("/(^|}|,|\s)($tagSwitch)/i", "StyleCompressor::processTagClass", $css);
		    $css = preg_replace_callback("/rgb\((\d)\s*?,\s*?(\d)\s*?,\s*?(\d)\)/i", "StyleCompressor::rgbToHex", $css);
		    $css = preg_replace_callback("/rgba\((\d)\s*?,\s*?(\d)\s*?,\s*?(\d),\s*?(\d)\)/i", "StyleCompressor::rgbToHex", $css);
            $css = "/* This file has been automatically modified to support Legacy Browsers */\n" . $css;
	    }
		
		if($interpolate)
		    $css = interpolate($css, true);
		
		return $css;
	}
	
	public function processFile($path){
		return self::processContent(file_get_contents($path));
	}
	
	public function addStyle($path){
		$this->addFile($path);
	}

}
?>