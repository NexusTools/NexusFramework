<?php
class CountryDataQuery extends CachedFileBase {

	private $k;
	private $v;

	public function __construct($keyNode, $valueNode) {
		$this->k = $keyNode;
		$this->v = $valueNode;
	}

	protected function getAdvancedID() {
		return $this->k.":".$this->v;
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
		$data = simplexml_load_file(dirname(__FILE__).DIRSEP."data".DIRSEP."iso3166.xml");
		header("Content-Type: text/plain");
		OutputFilter::resetToNative(false);

		$compiledData = Array();
		foreach ($data->children() as $child) {
			$compiledData[(string) $child[$this->k]] = (string) $child[$this->v];
		}

		print_r($compiledData);
		die();
	}

	protected function isShared() {
		return true;
	}

}
?>
