<?php
class GravatarUser {

    private $email;
    private static $domain = false;
    
    public function __construct($user){
        $this->email = $user->getEmail();
    }
    
    public static function getDatabase(){
        return self::$database ? self::$database : (self::$database = Database::getInstance());
    }
    
    public static function getAvatarDefault(){
        $path = dirname(__FILE__) . DIRSEP . "default.png";
        return $raw ? $path : Framework::getReferenceURI($path);
    }
    
    public function getAvatar($size=128){
    	if(!self::$domain)
    		self::$domain = PROTOCOL_SECURE ? "https://secure.gravatar.com/avatar/" : "https://secure.gravatar.com/avatar/";
    	
        return self::$domain . md5(strtolower(trim($this->email))) . "?d=" . urlencode(self::getAvatarDefault()) . "&s=" . $size;
    }

}
?>
