<?php
class Element {

	private $tagName;
	private $children = Array();
	private $attr;

	public function __construct($tag, $attr = Array(), $innerHTML = false) {
		$this->tagName = $tag;
		$this->attr = $attr;
		if ($innerHTML)
			array_push($this->children, $innerHTML);
	}

	public function appendChild($element) {
		array_push($this->children, $element);
	}

	public function prependChild($element) {
		array_unshift($this->children, $element);
	}

	public function toHTML() {
		$html = "";
		return $html;
	}

	public function __toString() {
		return $this->toHTML();
	}

}
?>
