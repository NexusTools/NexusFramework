<?php
class BBCEngine {

	private static $tags = Array();
	private static $database;

	public static function init() {
		self : $database = Database::getInstance();
	}

	public static function process($code) {
		$buffer = new OutputCapture(true);
		self::render($code);
		return $buffer->finish();
	}

	public static function render($code) {
		while (($pos = strpos($code, "[")) !== false) {
			echo nl2br(htmlentities(substr($code, 0, $pos)));
			$code = substr($code, $pos + 1);
			$end = strpos($code, "]");
			if ($end === false)
				break; // broken tag, stop parsing

			if (($eq = strpos($code, "=")) !== false && $eq < $end) { // [url=test]
				$arg = substr($code, $eq + 1, $end - $eq - 1); // test

				if (startsWith($arg, "\"") || startsWith($arg, "'"))
					$arg = substr($arg, 1);

				if (endsWith($arg, "\"") || endsWith($arg, "'"))
					$arg = substr($arg, 0, strlen($arg) - 1);

				$tag = strtolower(substr($code, 0, $eq));
			} else {
				$tag = strtolower(substr($code, 0, $end));
				$arg = "";
			}

			$code = substr($code, $end + 1);
			$far = strpos($code, "[/$tag]");

			if ($far === false) {
				echo "<span title='BBC Parsing Error' class='error bbc bbc-engine bbc-message bbc-error'>Closing `[/$tag]` Missing</span>";
				break;
			}

			$filler = substr($code, 0, $far);
			$code = substr($code, $far + strlen($tag) + 3);

			try {
				if (!array_key_exists($tag, self::$tags))
					throw new Exception("No Such Tag Registered `$tag`");
				$reflect = self::$tags[$tag];
				if (!($reflect instanceof ReflectionMethod))
					$reflect = new ReflectionMethod($reflect);
				$params = $reflect->getParameters();
				switch ($reflect->getNumberOfParameters()) {
				case 1:
					if ($arg)
						throw new Exception("`$tag` does not accept arguments.");
					$reflect->invoke(null, $filler);
					break;

				case 2:
					if (!$arg) {
						if (!$params[1]->isOptional())
							throw new Exception("`$tag` requires an argument.");
						$reflect->invoke(null, $filler);
					} else
						$reflect->invoke(null, $filler, $arg);
					break;

				default:
					throw new Exception("BBC tag processor malformed.");

				}

			} catch (Exception $e) {
				echo "<span class=\"error bbc bbc-engine bbc-message bbc-error\">";
				echo $e->getMessage();
				echo "</span>";
			}

		}

		echo nl2br(htmlentities($code));
	}

	public static function registerTag($tag, $processor) {
		if (array_key_exists($tag, self::$tags))
			throw new Exception("Processor for `$tag` already registered.");
		self::$tags[$tag] = $processor;
	}

	public static function _bold($filler) {
		echo "<b>";
		self::render($filler);
		echo "</b>";
	}

	public static function _italic($filler) {
		echo "<i>";
		self::render($filler);
		echo "</i>";
	}

	public static function _sup($filler) {
		echo "<sup>";
		self::render($filler);
		echo "</sup>";
	}

	public static function _underline($filler) {
		echo "<span style=\"text-decoration: underline\">";
		self::render($filler);
		echo "</span>";
	}

	public static function _font($filler, $face) {
		echo "<span style=\"font-family: ";
		echo htmlspecialchars($face);
		echo "\">";
		self::render($filler);
		echo "</span>";
	}

	public static function _size($filler, $size) {
		$size = $size * 1;
		if ($size < 4)
			$size = 4;
		if ($size > 20 && !User::isStaff())
			$size = 20;
		echo "<span style=\"font-size: ".$size."px\">";
		self::render($filler);
		echo "</span>";
	}

	public static function _colour($filler, $color) {
		echo "<span style=\"color: $color\">";
		self::render($filler);
		echo "</span>";
	}

	public static function _html($filler) {
		echo $filler;
	}

	public static function _code($filler, $type = "text") {
		echo "<pre type=\"";
		echo htmlspecialchars($type);
		echo "\">";
		echo htmlentities($filler);
		echo "</pre>";
	}

	public static function _p($filler) {
		echo "<p>";
		self::render($filler);
		echo "</p>";
	}

	public static function _title($filler, $level = 1) {
		$level = $level * 1;
		if ($level < 1)
			$level = 1;
		if ($level > 5)
			$level = 5;
		echo "<h$level>";
		self::render($filler);
		echo "</h$level>";
	}

	public static function _subtitle($filler) {
		self::_title($filler, 2);
	}

	public static function _url($filler, $path) {
		echo "<a href='";
		echo htmlspecialchars($path);
		echo "'>";
		self::render($filler);
		echo "</a>";
	}

	public static function _image($filler, $size = false) {
		echo "<img src='";
		echo htmlspecialchars($filler);
		echo "' />";
	}

	public static function _interpolate($filler) {
		if (!User::isAdmin())
			throw new Exception("Use of interplate is restricted to administrators only.");
		self::render(interpolate($filler, true));
	}

	public static function _eval($filler) {
		if (!User::isAdmin())
			throw new Exception("Use of eval is restricted to administrators only.");
		$value = Runtime::evaluate($filler);
		if (is_bool($value))
			return $value ? "true" : "false";
		else
			if (is_array($value) || is_object($value))
				$value = json_encode($value);

		self::render($value);
	}

	public static function _shade($filler, $color) {
		return "<span style=\"text-shadow: $color 0px 0px 5px;\">$filler</span>";
	}

}
BBCEngine::init();

BBCEngine::registerTag("p", "BBCEngine::_p");
BBCEngine::registerTag("b", "BBCEngine::_bold");
BBCEngine::registerTag("i", "BBCEngine::_italic");
BBCEngine::registerTag("u", "BBCEngine::_underline");
BBCEngine::registerTag("url", "BBCEngine::_url");
BBCEngine::registerTag("sup", "BBCEngine::_sup");
BBCEngine::registerTag("eval", "BBCEngine::_eval");
BBCEngine::registerTag("html", "BBCEngine::_html");
BBCEngine::registerTag("font", "BBCEngine::_font");
BBCEngine::registerTag("code", "BBCEngine::_code");
BBCEngine::registerTag("size", "BBCEngine::_size");
BBCEngine::registerTag("image", "BBCEngine::_image");
BBCEngine::registerTag("title", "BBCEngine::_title");
BBCEngine::registerTag("subtitle", "BBCEngine::_subtitle");
BBCEngine::registerTag("shade", "BBCEngine::_shade");
BBCEngine::registerTag("color", "BBCEngine::_colour");
BBCEngine::registerTag("colour", "BBCEngine::_colour");
BBCEngine::registerTag("interpolate", "BBCEngine::_interpolate");
?>
