<?php
class ModifiedImage extends CachedFileBase {

	const IgnoreAspectRatio = 0;
	const KeepAspectRatio = 1;

	private $operations = Array();
	private $inputImage;
	private $canvasSize;
	private $mimeType;
	private $transparent = false;
	private $quality;

	public function setTransparent($trans) {
		if ($trans)
			$this->mimeType = "image/png";
		$this->transparent = $trans;
	}

	public function __construct($path, $width = -1, $height = -1, $quality = 100) {
		if ($path instanceof ModifiedImage) {
			$path->getData();
			$path = $path->getStoragePath();
		}

		CachedFileBase::__construct($path);
		$this->inputImage = new ImageReference($path);
		$this->canvasSize = Array($width > 0 ? $width : $this->inputImage->getWidth(),
			$height > 0 ? $height : $this->inputImage->getHeight());
		$this->quality = $quality;

		switch ($this->inputImage->getType()) {
		case IMAGETYPE_GIF:
			$this->mimeType = "image/gif";
			break;

		case IMAGETYPE_PNG:
		case IMAGETYPE_WBMP:
		case IMAGETYPE_XBM:
		case IMAGETYPE_XPM:
			$this->mimeType = "image/png";
			break;

		case IMAGETYPE_JPEG:
		case IMAGETYPE_JPEG2000:
			$this->mimeType = "image/jpeg";
			break;

		default:
			throw new Exception("Unsupported Image Type");
		}
	}

	public function getWidth() {
		$this->canvasSize[0];
	}

	public function getHeight() {
		return $this->canvasSize[1];
	}

	public function getSize() {
		return $this->canvasSize;
	}

	public function getNaturalWidth() {
		return $this->inputImage->getWidth();
	}

	public function getNaturalHeight() {
		return $this->inputImage->getHeight();
	}

	public function getNaturalSize() {
		return $this->inputImage->getSize();
	}

	public function addOperation($operation, $arguments) {
		array_unshift($arguments, $operation);
		array_push($this->operations, $arguments);
	}

	public function addOperationRaw($operation) {
		array_push($this->operations, $operation);
	}

	public function prependOperation($operation, $arguments) {
		array_unshift($arguments, $operation);
		array_unshift($this->operations, $arguments);
	}

	public function prependOperationRaw($operation) {
		array_unshift($this->operations, $operation);
	}

	protected function updateAdvancedMeta(&$metaObject) {
	}

	protected function getAdvancedID() {
		return Framework::uniqueHash($this->operations, Framework::RawHash)
			.Framework::uniqueHash($this->canvasSize, Framework::RawHash).$this->quality;
	}

	public function isShared() {
		return true;
	}

	public function getMimeType() {
		return $this->mimeType;
	}

	public function getPrefix() {
		return "gd-modified-images";
	}

	protected function getOutputResource() {
		$operationEnvironment = Array();
		$outputImage = imagecreatetruecolor($this->canvasSize[0], $this->canvasSize[1]);
		$inputImageRes = $this->inputImage->loadResource();

		if ($this->transparent) {
			imagesavealpha($outputImage, true);
			imagealphablending($outputImage, false);
			imagefilledrectangle($outputImage, 0, 0,
				$this->canvasSize[0], $this->canvasSize[1],
				imagecolorallocatealpha($outputImage, 255, 255, 255, 0));
			imagecolortransparent($outputImage, imagecolorallocate($outputImage, 0, 0, 0));
		}

		foreach ($this->operations as $rawArgs) {
			$operation = array_shift($rawArgs);
			$args = Array();
			foreach ($rawArgs as $arg) {
				if ($arg instanceof ModifiedImage)
					array_push($args, $arg->getOutputResource());
				else
					if ($arg instanceof ImageReference)
						array_push($args, $arg->loadResource());
					else
						if (is_string($arg) && startsWith($arg, "{{")) {
							$arg = substr($arg, 2, strlen($arg) - 4);
							switch ($arg) {
							case "WIDTH":
								array_push($args, $this->inputImage->getWidth());
								break;

							case "HEIGHT":
								array_push($args, $this->inputImage->getHeight());
								break;

							case "DEST":
								array_push($args, $outputImage);
								break;

							case "SRC":
								array_push($args, $inputImageRes);
								break;

							case "BLACK":
								array_push($args, imagecolorallocate($outputImage, 0, 0, 0));
								break;

							default:
								throw new Exception("Unknown Dynamic Argument: $arg");
							}
						} else
							array_push($args, $arg);
			}
			call_user_func_array($operation, $args);
		}
		if (!$outputImage)
			throw new Exception("No Output");

		return $outputImage;
	}

	public function update() {
		$outputImage = $this->getOutputResource();

		$this->operations = Array();
		$this->inputImage = false;

		if ($outputImage) {
			switch ($this->mimeType) {
			case "image/jpeg":
				imagejpeg($outputImage, $this->getStoragePath(), $this->quality);
				break;

			case "image/gif":
				imagegif($outputImage, $this->getStoragePath());
				break;

			case "image/png":
				imagepng($outputImage, $this->getStoragePath(), 9, PNG_ALL_FILTERS);
				break;

			default:
				throw new Exception("Unknown Image Mime Type: ".$this->mimeType);
			}
		}
		return null;
	}

	public static function __callStatic($name, $arguments) {
		$instanceURI = false;
		$instanceURL = false;
		if (endsWith($name, "URI")) {
			$instanceURI = true;
			$name = substr($name, 0, strlen($name) - 3);
		}
		if (endsWith($name, "URL")) {
			$instanceURL = true;
			$name = substr($name, 0, strlen($name) - 3);
		}
		if (endsWith($name, "Transparent")) {
			$transparentMode = true;
			$name = substr($name, 0, strlen($name) - 11);
		} else
			$transparentMode = false;
		$method = new ReflectionMethod("ModifiedImage", "__$name");
		$method->setAccessible(true);
		$instance = $method->invokeArgs(null, $arguments);
		$instance->setTransparent($transparentMode);
		if ($instanceURI || $instanceURL) {
			$cleanName = basename($instance->getFilepath());
			if (ctype_upper(substr($cleanName, 0, 1)))
				$cleanName = ucfirst($name)."-".$cleanName;
			else
				$cleanName = "$name-$cleanName";
			return $instanceURL ? $instance->getReferenceURL($instance->getMimeType(), $cleanName) : $instance->getReferenceURI($instance->getMimeType(), $cleanName);
		}
		return $instance;
	}

	private static function __($path, $width, $height, $operations, $quality = 100) {
		$modifiedImage = new ModifiedImage($path, $width, $height, $quality);
		foreach ($operations as $operation)
			$modifiedImage->addOperationRaw($operation);
		return $modifiedImage;
	}

	public function rescale($width, $height = -1, $mode = self::IgnoreAspectRatio) {
		return ModifiedImage::scaled($this->getStoragePath(), $width, $height, $mode);
	}

	public function __toString() {
		return $this->getFullPath();
	}

	public function rescaleURI($width, $height = -1, $mode = self::IgnoreAspectRatio) {
		return $this->rescale($width, $height, $mode)->getReferenceURI();
	}

	public function rescaleURL($width, $height = -1, $mode = self::IgnoreAspectRatio) {
		return $this->rescale($width, $height, $mode)->getReferenceURL();
	}

	private static function __scaled($path, $width, $height = -1, $mode = self::IgnoreAspectRatio) {
		if ($height == - 1)
			$height = $width;

		$modifiedImage = new ModifiedImage($path, $width, $height);
		if ($mode == self::KeepAspectRatio) {
			$scaledWidth = $modifiedImage->getNaturalWidth();
			$scaledHeight = $modifiedImage->getNaturalHeight();
			if ($scaledWidth > $width) {
				$scaledHeight = ($width / $modifiedImage->getNaturalWidth()) * $modifiedImage->getNaturalHeight();
				$scaledWidth = $width;
			}
			if ($scaledHeight > $height) {
				$scaledWidth = ($height / $modifiedImage->getNaturalHeight()) * $modifiedImage->getNaturalWidth();
				$scaledHeight = $height;
			}
		} else {
			$scaledWidth = $width;
			$scaledHeight = $height;
		}
		$modifiedImage->addOperationRaw(Array("imagecopyresampled", "{{DEST}}", "{{SRC}}", floor($width / 2 - $scaledWidth / 2), floor($height / 2 - $scaledHeight / 2), 0, 0, floor($scaledWidth), floor($scaledHeight), "{{WIDTH}}", "{{HEIGHT}}"));
		return $modifiedImage;
	}

	private static function __tinted($input, $rgb = Array(0, 255, 0)) {
		$modifiedImage = new ModifiedImage($input);
		$modifiedImage->addOperationRaw(Array("ModifiedImage::tint", "{{DEST}}", "{{SRC}}", $rgb));
		return $modifiedImage;
	}

	private static function __itinted($input, $rgb = Array(0, 255, 0)) {
		$modifiedImage = new ModifiedImage($input);
		$modifiedImage->addOperationRaw(Array("ModifiedImage::tint", "{{DEST}}", "{{SRC}}", $rgb, true));
		return $modifiedImage;
	}

	public static function tint($out, $in, $rgb, $invert = false) {
		$xSize = imagesx($in);
		$ySize = imagesy($in);

		$rgb = Array((float) $rgb[0] / 255.0, (float) $rgb[1] / 255.0, (float) $rgb[2] / 255.0);

		for ($x = 0; $x < $xSize; $x++) {
			for ($y = 0; $y < $ySize; $y++) {
				$pixel = imagecolorsforindex($in, imagecolorat($in, $x, $y));

				if ($invert) {
					$pixel['red'] = 255 - $pixel['red'];
					$pixel['green'] = 255 - $pixel['green'];
					$pixel['blue'] = 255 - $pixel['blue'];
					$rgb[0] = 1 - $rgb[0];
					$rgb[1] = 1 - $rgb[1];
					$rgb[2] = 1 - $rgb[2];
				}
				$pixel['red'] *= $rgb[0];
				$pixel['green'] *= $rgb[1];
				$pixel['blue'] *= $rgb[2];
				if ($invert) {
					$pixel['red'] = 255 - $pixel['red'];
					$pixel['green'] = 255 - $pixel['green'];
					$pixel['blue'] = 255 - $pixel['blue'];
				}

				imagesetpixel($out, $x, $y, imagecolorallocatealpha($out,
					$pixel['red'], $pixel['green'],
					$pixel['blue'], $pixel['alpha']));
			}
		}
	}

}
?>
