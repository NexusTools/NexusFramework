<?php
class ExtensionInfo extends CachedFile {


	public function __construct($path){
		parent::__construct($path);
	}
	
	public function getMimeType(){
		return "";
	}
	
	public function getPrefix(){
		return "extension";
	}
	
	protected function update()
	{
		$data = Array("classes" => Array(), "files" => Array());
		
		foreach(glob($this->getFilepath() . "/*.php") as $phpfile)
		{
			$phpinfo = new PHPFile($phpfile);
			$data['files'][] = $phpinfo;
			foreach($phpinfo->getClasses() as $class)
				$data['classes'][$class->getName()] = $class;
		}
		
		header("Content-Type: text/plain");
		print_r($data);
		exit;
	}
	
}
?>
