<?php
class CountryDataLookup extends CachedFile {

	private $k;
	private $v;
	private $kv;

	public function __construct($keyNode, $keyValue, $valueNode) {
		CachedFileBase::__construct(dirname(__FILE__).DIRSEP."data".DIRSEP."iso3166.xml");
		$this->k = $keyNode;
		$this->v = $valueNode;
		$this->kv = $keyValue;
	}

	protected function getAdvancedID() {
		return $this->k.":".$this->v.":".$this->kv;
	}

	protected function updateAdvancedMeta(&$metaObject) {
		$metaObject['nodes'] = Array("key" => $this->k, "value" => $this->k);
	}

	public function getMimeType() {
		return "country/data-lookup";
	}

	public function getPrefix() {
		return "country-data";
	}

	protected function update() {
		$data = new CountryDataParser($this->k, $this->v);
		$data = $data->getData();

		return $data[$this->kv];
	}

	protected function isShared() {
		return true;
	}

}
?>
