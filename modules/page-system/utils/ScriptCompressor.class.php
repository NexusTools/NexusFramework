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
	
	protected static function optimizeBlock($block, &$replacements) {
		echo " --- Optimizing Code Block --- \n$block\n";
		
		$num = 0;
		$offset = 0;
		$newblock = "";
		while(preg_match('/(^|\n|;)\s*var\s+([\$_a-zA-Z][\$_\w]*)\s*(=|;|\n|$)/', $block, $matches, PREG_OFFSET_CAPTURE, $offset)) {
			do {
				$newVar = chr(97 + $num);
				$num ++;
			} while(in_array($newVar, $replacements, true));
			$replacements[$matches[2][0]] = $newVar;
			
			$newoffset = $matches[0][1] + strlen($matches[0][0]);
			$chunk = substr($block, $offset, $newoffset-$offset);
			foreach($replacements as $from => $to) {
				echo '/([^\$_\w\.])' . preg_quote($from) . '([^\$_\w])/' . "\n";
				$chunk = preg_replace('/([^\$_\w\.]\s*)' . preg_quote($from) . '([^\$_\w])/', '$1<span style="color: red">' . $to . '</span>$2', $chunk);
			}
			$offset = $newoffset;
			$newblock .= $chunk;
		}
		
		$newoffset = strlen($block);
		if($newoffset > $offset) {
			$chunk = substr($block, $offset, $newoffset-$offset);
			foreach($replacements as $from => $to) {
				echo '/([^\$_\w])' . preg_quote($from) . '([^\$_\w])/' . "\n";
				$chunk = preg_replace('/([^\$_\w\.])' . preg_quote($from) . '([^\$_\w])/', '$1<span style="color: red">' . $to . '</span>$2', $chunk);
			}
			$newblock .= $chunk;
		}
		
		echo " --- Complete --- \n";
		var_dump($replacements);
		echo $newblock;
	
		return $newblock;
	}
	
	public static function optimize($code) {
		OutputFilter::startRawOutput(true);
		echo "<pre> ---=== Input Version ===--- \n$code\n\n";
		set_time_limit(5);
		
		$num = 0;
		$offset = 0;
		$newcode = "";
		$blockIndex = 0;
		$min = ord("a");
		$stack = array();
		$lastBlockOffset = 0;
		$replacements = array();
		echo " ---=== Processing Output ===--- \n";
		while($offset > -1) {
			if(preg_match('/("|\'|{|}|(^|\s*|\n|[^\$_a-zA-Z][a-z]+\s*([\$_a-zA-Z][\$_\w]*)?\([^\)]*\)\s*{)/', $code, $matches, PREG_OFFSET_CAPTURE, $offset)) {
				$offset = $matches[0][1];
				
				var_dump($matches);
				if($matches[1][0] == '"') {
					if($offset > $lastBlockOffset) {
						$newcode .= self::optimizeBlock(substr($code, $lastBlockOffset, $offset-$lastBlockOffset), $replacements);
						$lastBlockOffset = $offset;
					}
				
					$newcode .= "<span style='color: blue'>";
					preg_match('/"/', $code, $matches, PREG_OFFSET_CAPTURE, $matches[1][1]+1);
					$offset = $matches[0][1]+strlen($matches[0][0]);
					$newcode .= htmlentities(substr($code, $lastBlockOffset, $offset-$lastBlockOffset));
					$newcode .= "</span>";
					$lastBlockOffset = $offset;
					continue;
				} else if($matches[1][0] == "'") {
					if($offset > $lastBlockOffset) {
						$newcode .= self::optimizeBlock(substr($code, $lastBlockOffset, $offset-$lastBlockOffset), $replacements);
						$lastBlockOffset = $offset;
					}
					
					$newcode .= "<span style='color: blue'>";
					preg_match("/'/", $code, $matches, PREG_OFFSET_CAPTURE, $matches[1][1]+1);
					$offset = $matches[0][1]+strlen($matches[0][0]);
					$newcode .= htmlentities(substr($code, $lastBlockOffset, $offset-$lastBlockOffset));
					$newcode .= "</span>";
					$lastBlockOffset = $offset;
					continue;
				} else if($matches[1][0] == "{") {
					$offset ++;
					if($offset > $lastBlockOffset) {
						$newcode .= self::optimizeBlock(substr($code, $lastBlockOffset, $offset-$lastBlockOffset), $replacements);
						$lastBlockOffset = $offset;
					}
					
					$blockIndex ++;
					echo "\n -- Entered Block -- \n";
					$newcode .= "<sup>$blockIndex - " . count($stack) . "</sup>";
				} else if($matches[1][0] == "}") {
					$offset ++;
					if($offset > $lastBlockOffset) {
						$newcode .= self::optimizeBlock(substr($code, $lastBlockOffset, $offset-$lastBlockOffset), $replacements);
						$lastBlockOffset = $offset;
					}
				
					$blockIndex --;
					if($blockIndex < 0) {
						echo "\n -- Left Function Block -- \n";
						
						$context = array_pop($stack);
						echo " -- Popping Context -- \n";
						if($context) {
							var_dump($context);
							
							$blockIndex = $context['blockIndex'];
							$replacements = $context['replacements'];
						} else
							$newcode .= "<sup>EMPTY STACK</sup> ";
						$newcode .= "<sup><b>$blockIndex</b> - " . (count($stack)+1) . "</sup>";
					} else {
						echo "\n -- Left Block -- \n";
						$newcode .= "<sup>" . ($blockIndex+1) . " - " . count($stack) . "</sup>";
					}
				} else { // Assume function
					if($offset > $lastBlockOffset) {
						$newcode .= self::optimizeBlock(substr($code, $lastBlockOffset, $offset-$lastBlockOffset), $replacements);
						$lastBlockOffset = $offset;
					}
					
					$context = array(
						"blockIndex" => $blockIndex,
						"replacements" => $replacements
					);
					echo " -- Pushing Context -- \n";
					array_push($stack, $context);
					var_dump($context);
					
					echo "\n -- Entered Function Block -- \n";
					
					$offset = $matches[0][1] + strlen($matches[0][0]);
					if($offset > $lastBlockOffset) {
						$newcode .= self::optimizeBlock(substr($code, $lastBlockOffset, $offset-$lastBlockOffset), $replacements);
						$lastBlockOffset = $offset;
					}
					$newcode .= "<sup><b>$blockIndex</b> - " . count($stack) . "</sup>";
					$blockIndex = 0;
				}
				
			} else
				break;
		}
		
		$offset = strlen($code);
		if($offset > $lastBlockOffset) {
			$newcode .= self::optimizeBlock(substr($code, $lastBlockOffset, $offset-$lastBlockOffset), $replacements);
			$lastBlockOffset = $offset;
		}
		
		echo "\n\n ---=== Output Version ===--- \n$newcode\n\n";
		exit;
		
		return $newcode;
	}

	public static function processContent($code, $compress = true, $interpolate = true, $optimize =true) {
		$volatile = preg_match("/^@volatile\s/i", $code);
		if ($volatile)
			$code = substr($code, 10);
		else {
			if ($interpolate)
				$code = interpolate($code);
			
			if (!DEBUG_MODE && !DEV_MODE) {
				if($compress)
					$code = self::compress($code);
					
				// Experimental
				/*if($optimize)
					$code = self::optimize($code);*/
			}
		}

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
