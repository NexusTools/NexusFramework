<?php
class ImageReference {

	private $type;
	private $size;
	private $path;
	private $gdImage = false;

	public function __toString() {
		return $this->path;
	}

	public function __construct($image) {
		$this->path = fullpath($image);
		if (!is_file($this->path))
			throw new Exception("Image File Missing");

		$this->size = getimagesize($this->path);

		if (!$this->size || $this->size[0] < 1 || $this->size[1] < 1)
			throw new Exception("Corrupt Image");

		$this->type = $this->size[2];
		unset($this->size[3]);
		unset($this->size[2]);
	}

	public function getType() {
		return $this->type;
	}

	public function getFilepath() {
		return $this->path;
	}

	public function getSize() {
		return $this->size;
	}

	public function getWidth() {
		return $this->size[0];
	}

	public function getHeight() {
		return $this->size[1];
	}

	public function loadResource() {
		if (!$this->gdImage) {
			switch ($this->type) {
			case IMAGETYPE_GIF:
				$this->gdImage = imagecreatefromgif($this->getFilepath());
				break;

			case IMAGETYPE_PNG:
				$this->gdImage = imagecreatefrompng($this->getFilepath());
				break;

			case IMAGETYPE_WBMP:
				$this->gdImage = imagecreatefromwbmp($this->getFilepath());
				break;

			case IMAGETYPE_XBM:
				$this->gdImage = imagecreatefromxbm($this->getFilepath());
				break;

			case IMAGETYPE_XPM:
				$this->gdImage = imagecreatefromxpm($this->getFilepath());
				break;

			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				$this->gdImage = imagecreatefromjpeg($this->getFilepath());
				break;

			default:
				throw new Exception("Unsupported or Corrupt Image");
			}
		}

		return $this->gdImage;
	}

}
?>
