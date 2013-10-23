<?php
class StateDataParser extends CachedFile {

	private $c;
	private $r;

	public function __construct($country, $reverse = false) {
		CachedFileBase::__construct(dirname(__FILE__).DIRSEP."data".DIRSEP."iso3166_2.xml");
		$this->c = $country;
		$this->r = $reverse;
	}

	protected function getAdvancedID() {
		return $this->c.($this->r ? "-reversed" : "");
	}

	protected function updateAdvancedMeta(&$metaObject) {
		$metaObject['nodes'] = Array("key" => $this->k, "value" => $this->k);
	}

	public function getMimeType() {
		return "country/data";
	}

	public function getPrefix() {
		return "country-data";
	}

	protected function update() {
		$data = simplexml_load_file($this->getFilePath());

		$compiledData = Array();
		foreach ($data->children() as $child) {
			if ((string) $child['code'] === $this->c) {
				$c = $this->c;
				foreach ($child->children() as $tChild) {
					if ($tChild->getName() == "iso_3166_subset")
						foreach ($tChild->children() as $sChild) {
							$code = (string) $sChild['code'];
							$name = (string) $sChild['name'];

							if (preg_match("/^$c\-(\w+)/", $code, $matches) && $code = $matches[1]) {
								if ($this->r)
									$compiledData[$name] = $code;
								else
									$compiledData[$code] = $name;
							}
						}

				}
				break;
			}

		}
		return $compiledData;
	}

	protected function isShared() {
		return true;
	}

}
?>
