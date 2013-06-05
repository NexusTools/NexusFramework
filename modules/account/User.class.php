<?php
class User {

    private static $usercache = Array();
    private static $extensions = Array();
    private static $backend = false;
    private static $instance = false;
    
    const FETCH_ALLOW_ROOT = 1;
    const FETCH_HIDE_SYSTEM = true;
    const FETCH_ANY_USER = false;
    const EMAIL_REGEXP = "/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
    const USERNAME_REGEXP = "/^[a-z]+([_\\.-][a-z0-9]+)*?$/i";
    
    public static function validateEmail($email) {
        return preg_match(self::EMAIL_REGEXP, $email);
    }
    
    public static function validateUsername($username) {
        return preg_match(self::USERNAME_REGEXP, $username);
    }
    
    private static function verifyLoggedUser(){
        self::$instance = self::fetch($_SESSION['user'], self::FETCH_ALLOW_ROOT);
        if(!self::$instance->isValid()) {
            self::$instance = self::fetch(-2, self::FETCH_ANY_USER);
            unset($_SESSION['user']); // User Erased or Otherwise...
        }
    }
    
    public static function getInstance(){
        if(self::$instance === false) {
            if(self::$backend && isset($_SESSION['user']))
                self::verifyLoggedUser();
            else
                self::$instance = self::fetch(-2, self::FETCH_ANY_USER);
        }
        
        return self::$instance;
    }
    
    public static function isLogged(){ // Only human users and root count as being logged in
        return self::getInstance()->getID() >= 0;
    }
    
    public static function setActiveUser($identifier, $allowSystemUsers=false){
        self::$instance = self::fetch($identifier, !$allowSystemUsers);
    }
    
    public static function setBackend($backend){
        if(self::$backend)
            throw new Exception("User Backend Already Set");
            
        try {
            $refClass = new ReflectionClass($backend);
            if($refClass->isSubclassOf("UserBackend"))
                self::$backend = $backend;
            else
                throw new Exception("Not a subclass of UserBackend");
                
            self::verifyLoggedUser();
        } catch(Exception $e){
            throw new Exception("$backend is not a valid UserBackend implementation", 0, $e);
        }
    }
    
    public static function fetch($identifier, $mode=self::FETCH_HIDE_SYSTEM){
        if($identifier instanceof UserInterface)
            return $identifier;
            
        if(!is_numeric($identifier))
            $identifier = self::resolveUserID($identifier);
        
        if($mode) {
            if($identifier < -1 || ($identifier == 0 && $mode !== self::FETCH_ALLOW_ROOT))
                $identifier = -1;
        } else if($identifier < -3)
            $identifier = -1;
        
        if(array_key_exists($identifier, self::$usercache))
            return self::$usercache[$identifier];
        
        switch($identifier){
            case -3:
                $user = SystemUser::instance();
                break;
            
            case -2:
                $user = GuestUser::instance();
                break;
        
            case -1: // NullUser
                $user = NullUser::instance();
                break;
                
            case 0: // RootUser
                $user = RootUser::instance();
                break;
                
            default:
                $user = self::backendIfAvailable("getUserForID", Array($identifier));
        }
            
        return $user instanceof UserInterface ? (self::$usercache[$identifier] = $user) : self::fetch(-1);
    }
    
    public static function getDatabase(){
        return self::backendIfAvailable("getDatabase");
    }
    
    protected static function backendIfAvailable($callable, $arguments=Array(), $badReturn=null){
        if(!self::$backend)
            return $badReturn;
        
        return call_user_func_array(Array(self::$backend, $callable), $arguments);
    }
    
    public static function resolveUserIDByUsername($identifier){
        switch(strtolower($identifier = "$identifier")) {
            case "system":
                return -3;
                
            case "guest":
                return -2;
        
            case "null":
                return -1;
                
            case "root":
            case "admin":
            case "administrator":
                return 0;
            
            default:
                $id = self::backendIfAvailable("resolveUserIDByUsername", Array($identifier), -1);
                return is_numeric($id) && $id > 0 ? $id : -1;
        }
    }
    
    public static function resolveUserIDByEmail($identifier){
        if(RootUser::instance()->getEmail() == $identifier)
            return 0;
        else {
            $id = self::backendIfAvailable("resolveUserIDByEmail", Array($identifier), -1);
            return is_numeric($id) && $id > 0 ? $id : -1;
        }
    }
    
    public static function resolveUserID($identifier){
        if($identifier instanceof UserInterface)
            return $identifier->getID();
    
        if(is_numeric($identifier))
            return ($identifier < -3 ? -1 : $identifier);
    
        if(self::validateUsername($identifier))
            return self::resolveUserIDByUsername($identifier);
        else if(self::validateEmail($identifier))
            return self::resolveUserIDByEmail($identifier);
        
        return -1;
    }
	
	public static function initAllBackends(){
		foreach(self::$usercache as $user)
			$user->initBackends();
	}
    
    public static function exists($identifier){
        return self::resolveUserID($identifier) != -1;
    }
    
    public static function setLoggedUser($user){
        $user = self::fetch($user);
		
		if($user->isVerified()) {
		    self::setActiveUser($user);
			$_SESSION['user'] = $user->getID();
			return true;
		} elseif(isset($_SESSION['user']))
			unset($_SESSION['user']);
		
		return false;
	}
	
	public static function getRootUser(){
		return self::fetch(0, self::FETCH_ALLOW_ROOT);
	}
    
    public static function login($id, $pass, $allowRoot=true){
        $user = self::checkLogin($id, $pass, $allowRoot);
        return $user ? self::setLoggedUser($user) : false;
	}
	
	public static function register($user, $pass, $email, $requireVerification=true){
	    if(!self::validateUsername($user))
	        throw new Exception("Bad Username");
	    if(!self::validateEmail($email))
	        throw new Exception("Bad Email");
		
	    if(self::resolveUserIDByUsername($user) != -1)
	        return false;
	    
	    if(self::resolveUserIDByEmail($email) != -1)
	        return 0;
	    
	    return self::backendIfAvailable("register", Array($user, $pass, $email, $requireVerification));
	}
	
	public static function getStaffIDs($minLevel = 1){
	    return self::backendIfAvailable("getStaffIDs", Array($minLevel), Array());
	}
	
	public static function checkLogin($id, $pass, $allowRoot=true){
	    $user = self::fetch($id, $allowRoot ? self::FETCH_ALLOW_ROOT : self::FETCH_HIDE_SYSTEM);
        
        if($user->isVerified() && $user->checkPassword($pass)) 
            return $user;
        else
            return false;
	}

	public static function logout(){
		self::setLoggedUser(-1);
	}

    public static function __callStatic($name, $arguments) {
		if(endsWith($name, "ById") || endsWith($name, "ByID"))
			return self::fetch(array_shift($arguments), self::FETCH_ANY_USER)->__call(substr($name, 0, strlen($name)-4), $arguments);
	    else if(endsWith($name, "As") || endsWith($name, "As"))
			return self::fetch(array_shift($arguments), self::FETCH_ANY_USER)->__call(substr($name, 0, strlen($name)-2), $arguments);
	    else if(preg_match("/As([\d\w]+)$/", $name, $matches)) {
	        $name = substr($name, 0, strlen($name)-2-strlen($matches[1]));
	        return self::fetch($matches[1], self::FETCH_ANY_USER)->__call($name, $arguments);
	    }
		
		return self::getInstance()->__call($name, $arguments);
    }

}
?>
