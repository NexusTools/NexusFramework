<?php
class CountryDataParser extends CachedFile {

	private $k;
	private $v;

	public function __construct($keyNode, $valueNode) {
		CachedFileBase::__construct(dirname(__FILE__) . DIRSEP . "data" . DIRSEP . "iso3166.xml");
		$this->k = $keyNode;
		$this->v = $valueNode;
	}
	
	protected function getAdvancedID(){
	    return $this->k . ":" . $this->v;
	}
	
	protected function updateAdvancedMeta(&$metaObject){
		$metaObject['nodes'] = Array("key" => $this->k, "value" => $this->k);
	}
	
	public function getMimeType() {
		return "country/data";
	}
	
	public function getPrefix(){
		return "country-data";
	}
	
	protected function update(){
		$data = simplexml_load_file($this->getFilePath());
		$compiledData = Array();
		foreach($data->children() as $child) {
			if(!$this->v)
				array_push($compiledData, (string)$child[$this->k]);
			else
				$compiledData[(string)$child[$this->k]] = (string)$child[$this->v];
		}
		
		return $compiledData;
	}
	
	protected function isShared(){
		return true;
	}

}
?>
