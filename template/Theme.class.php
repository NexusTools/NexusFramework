<?php
class Theme extends CachedFile {

    private static $activePath;
    
    public static function initActivePath(){
    	self::$activePath = INDEX_PATH . "themes" . DIRSEP . THEME;
    }
    
    public static function getPath(){
        return self::$activePath;
    }
	
	public function __construct($path){
		parent::__construct($path);
	}
	
	public function getMimeType(){
		return "inode/directory";
	}
	
	public function getPrefix(){
		return "theme";
	}
	
	protected function update(){
		$data = Array();
		if(file_exists($this->getFilepath() . "head.inc.php"))
			$data['hs'] = "head.inc.php";
		
		if(file_exists($this->getFilepath() . "body.head.inc.php"))
			$data['bhs'] = "body.head.inc.php";
		else
			throw new IOException($this->getFilepath() . "body.head.inc.php", IOException::NotFound, "Missing Theme Body Header");
		
		if(file_exists($this->getFilepath() . "body.foot.inc.php"))
			$data['bfs'] = "body.foot.inc.php";
		else
			throw new IOException($this->getFilepath() . "body.foot.inc.php", IOException::NotFound, "Missing Theme Body Footer");
			
		$data['classes'] = Array();
		if(file_exists($this->getFilepath() . "classes")){
			
			foreach(glob($this->getFilepath() . "classes/*.class.php", GLOB_NOSORT) as $classfile){
				$handle = fopen($classfile, "r");
				
				if (is_resource($handle)) {
					while (($buffer = fgets($handle, 4096)) !== false) {
						if(startsWith($buffer, "class ")){
							$parts = explode(" ", $buffer);
							$data['cl'][$parts[1]] = substr($classfile, strlen($this->getFilepath()));
							break;
						} else if(startsWith($buffer, "abstract class ")) {
							$parts = explode(" ", $buffer);
							$data['cl'][$parts[2]] = substr($classfile, strlen($this->getFilepath()));
							break;
						}
					}
					fclose($handle);
				} else
					throw new IOException($classfile, IOException::ReadAccess);
			}
		}
		
		return $data;
	}
	
	protected function isShared(){
		return true;
	}
	
	public function initialize(){
		if($this->hasKey('hs')) 
			require_chdir($this->getValue('hs'), $this->getFilepath());
		
		if($this->hasKey("cl"))
			ClassLoader::registerClasses($this->getValue("cl"));
		self::$activePath = $this->getFilepath();
	}
	
	public function runHeader(){
		require_chdir($this->getValue('bhs'), $this->getFilepath());
	}
	
	public function runFooter(){
		require_chdir($this->getValue('bfs'), $this->getFilepath());
	}

} Theme::initActivePath();
?>
