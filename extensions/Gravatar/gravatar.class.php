<?php
class GravatarUser {

    private $email;
    private static $domain = false;
    private static $defaultPath = false;
    
    public function __construct($user){
        $this->email = $user->getEmail();
    }
    
    public static function getDatabase(){
        return self::$database ? self::$database : (self::$database = Database::getInstance());
    }
    
    public static function getAvatarDefault(){
        return Framework::getReferenceURI(dirname(__FILE__) . DIRSEP . "default.png");
    }
    
    public function getAvatar($size=128){
    	if(!self::$domain)
    		self::$domain = PROTOCOL_SECURE ? "https://secure.gravatar.com/avatar/" : "https://secure.gravatar.com/avatar/";
    	
        return self::$domain . md5(strtolower(trim($this->email))) . "?d=" . urlencode(
        								Framework::getReferenceURL(dirname(__FILE__) . DIRSEP . "default.png")
        										) . "&s=" . $size;
    }

}
?>
