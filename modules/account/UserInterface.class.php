<?php
abstract class UserInterface {

    private $id;
    private $email;
    private $level;
    private $username; // Must be a valid unix username
    private $backends = Array();
    private $methodcache = Array();
    private static $extensions = Array();
    private static $staticmethodcache = Array();
    private static $defaultmethodcache = Array();
    private static $defaultMethod = false;
    
    public function initBackends(){
		if($this->isValid())
			foreach(self::$extensions as $extension)
				$this->__getBackend($extension);
	}
    
    public static function levelString($level){
		if($level >= 6)
			return "Root";
		else if($level >= 5)
			return "System";
		else if($level >= 4)
			return "Owner";
		else if($level >= 3)
			return "Super Admin";
		else if($level >= 2)
			return "Admin";
		else if($level >= 1)
			return "Staff";
		else if($level >= 0)
			return "Member";
		else if($level >= -1)
			return "Unverified";
        else if($level >= -2)
			return "Disabled";
		else
		    return "Garbage"; 
	}
	
	public function getLevelString(){
	    return self::levelString($this->getLevel());
	}
    
    public static function longLevelString($level){
		if($level >= 6)
			return "Root Account";
		else if($level >= 5)
			return "System Account";
		else if($level >= 4)
			return "Website Owner";
		else if($level >= 3)
			return "Super Administrator";
		else if($level >= 2)
			return "Administrator";
		else if($level >= 1)
			return "Website Staff";
		else if($level >= 0)
			return "Active Member";
		else if($level >= -1)
			return "Unverified";
        else if($level >= -2)
			return "Banned Account";
		else
		    return "Corrupt Account"; 
	}
	
	public function getLongLevelString(){
	    return self::longLevelString($this->getLevel());
	}
	
	public function setLogged(){
		User::setLoggedUser($this);
	}
	
	public function setLevel($level){
	    if($this->setLevelImpl($level)){
	        $this->level = $level;
	        return true;
	    }
	    return false;
	}
	
	public function setEmail($email){
	    if($this->setEmailImpl($email)){
	        $this->email = $email;
	        return true;
	    }
	    return false;
	}
	
	public function setUsername($username){
	    if($this->setUsernameImpl($username)){
	        $this->username = $username;
	        return true;
	    }
	    return false;
	}
	
	protected abstract function registerDateImpl();
	protected abstract function setLevelImpl($level);
	protected abstract function setEmailImpl($email);
	protected abstract function setUsernameImpl($username);
	public abstract function setPassword($password);
	public abstract function checkPassword($password);
	
	public function isVerified(){
		return $this->level > -1;
	}
	
	public function isDisabled(){
		return $this->level < -1;
	}
	
	public function isStaff(){
		return $this->level > 0;
	}
	
	public function isAdmin(){
		return $this->level > 1;
	}
	
	public function isSuperAdmin(){
		return $this->level > 2;
	}
	
	public function isOwner(){
		return $this->level > 3;
	}
	
	public function isRoot(){
		return $this->getID() == 0;
	}
	
	public function isSystem(){
	    return $this->getID() == -3;
	}
	
	public function isGuest(){
	    return $this->getID() == -2;
	}
	
	public function isEnabled(){
		return $this->level >= 0;
	}
	
	public function getLevel(){
		return $this->level;
	}
    
    public function registerExtension($class){
        self::$extensions[] = $class;
    }
    
    protected function __construct($id, $email, $username, $level=0){
        $this->id = $id;
        $this->email = $email;
        $this->username = $username;
        
        if($level > 4 && !$this->isSystem() && !$this->isRoot())
            $level = 4;
        $this->level = $level;
    }
    
    public function getID(){
        return $this->id;
    }
    
    public function getEmail(){
        return $this->email;
    }
    
    public function getUsername(){
        return $this->username;
    }
    
    protected function _getFullName(){
		return ucfirst($this->username);
	}
	
	public function getRegisterDate() {
		return $this->registerDateImpl();
	}
	
	public function getRegisterString() {
		return StringFormat::formatDate($this->getRegisterDate(), false);
	}
    
    protected function _getDisplayName(){
		return ucfirst($this->username);
	}
	
	public function run($method, $arguments=Array()){
	    $exception = false;
	    
	    $oldUser = User::getInstance();
	    User::setActiveUser($this);
	    try {
	        $ret = call_user_func_array($method, $arguments);
	    } catch(Exception $e){
	        $exception = new Exception("Error while running methods as `$this`", 0, $e);
	    }
	    
	    User::setActiveUser($oldUser);
	    if($exception)
	        throw $exception;
	        
	    return $ret;
	}
	
	public function describe(){
	    return $this->getID() . "#" . $this->getUsername() . "[" . $this->getLevel() . "]";
	}
    
    public function isValid(){ // Is a valid human or root user?
        return $this->getID() >= 0;
    }
    
    public function __toString(){
        return $this->__call("getDisplayName");
    }
    
    private function __getBackend($classname){
        if(!$this->isValid())
            throw new Exception("Cannot use backend instances with non-human user accounts");
    
		if(!array_key_exists($classname, $this->backends))
			$this->backends[$classname] = new $classname($this);
			
		return $this->backends[$classname];
	}
	
	protected static function defaultCallback(){
	    return false;
	}
	
	public function isCurrentUser(){
        return User::getID() == $this->getID();
    }
	
	private static function getDefaultCallback(){
	    if(!self::$defaultMethod) {
	        self::$defaultMethod = new ReflectionMethod("UserInterface::defaultCallback");
	        self::$defaultMethod->setAccessible(true);
	    }
	    return self::$defaultMethod;
	}
    
    public function __call($name, $arguments=Array()) {
        if(!$this->isValid() && array_key_exists($name, self::$defaultmethodcache))
            $method = self::$defaultmethodcache[$name];
        else if(array_key_exists($name, self::$staticmethodcache))
    	    $method = self::$staticmethodcache[$name];
    	else if(!$method && array_key_exists($name, $this->methodcache)) {
    	    $method = $this->methodcache[$name];
    	    $thisObject = $method[1];
    	    $method = $method[0];
    	} else {
    	    $thisClass = get_class($this);
    	    $method = false;
    	}
    	
    	if(!$method) {
        	try{
			    $method = new ReflectionMethod($thisClass, $name);
			    if(!$method->isPublic())
			        throw new Exception("Method must be public");
			    if(!$method->isStatic())
				    $thisObject = $this;
		    } catch(Exception $e) {
		        $method = false;
		    }
		    
		    if(!$method) {
		        try{
			        $method = new ReflectionMethod($thisClass, "_$name");
			        if(!$method->isProtected())
			            throw new Exception("Method must be protected");
			        $method->setAccessible(true);
			        if(!$method->isStatic())
				        $thisObject = $this;
		        }catch(Exception $e){
		            $method = false;
		        }
		    	
		        if($this->isValid() || !$method) {
		        	$newMethod = false;
	                foreach(self::$extensions as $extension) {
	                	$newThisObject = $thisObject;
		                try{
			                $newMethod = new ReflectionMethod($extension, $name);
			                if($newMethod->isPrivate())
			                    throw new Exception("Method must not be private");
			                if($newMethod->isProtected())
			                    $newMethod->setAccessible(true);
			                if(!$newMethod->isStatic()) {
			                    if(!$this->isValid()) {
			                        try {
			                            $newMethod = new ReflectionMethod($extension, $name . "Default");
			                            $newMethod->setAccessible(true);
			                        } catch(Exception $e){
			                            $newMethod = self::getDefaultCallback();
			                        }
			                        self::$defaultmethodcache[$name] = $newMethod;
			                        
			                        return $newMethod->invokeArgs(null, $arguments);
			                    }
			                    
				                $newThisObject = $this->__getBackend($extension);
				            }
			                break;
		                }catch(ReflectionException $e){
		                    $newMethod = false;
		                }
	                }
	                
	                if($newMethod) {
			        	$method = $newMethod;
			        	$thisObject = $newThisObject;
			        }
	            }
	            
			        
	        }
	        
	        if($method) {
               if($method->isStatic())
	              self::$staticmethodcache[$name] = $method;
	          else
	              $this->methodcache[$name] = Array($method, $thisObject);
            }
	    }

		if($method){
			if($method->isStatic())
				return $method->invokeArgs(null, $arguments);
			else {
			    if($this != $thisObject && $this->getID() < 0)
			        return false;
			    
				return $method->invokeArgs($thisObject, $arguments);
		    }
		} else
			throw new Exception("Call to undefined method $thisClass::$name()");
    }

}
?>
