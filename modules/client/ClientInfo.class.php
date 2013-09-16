<?php
class ClientInfo {

	private static $remoteAddr;
	private static $reservedIPs;
	private static $uniqueID = false;
	private static $uniqueDomainID = false;

	public static function validateIP($addr){
		if (empty($addr) || !($addr = ip2long($addr)))
			return false;
			
		if(!self::$reservedIPs)
			self::$reservedIPs = array(
										array(0,50331647),
										array(167772160,184549375),
										array(2130706432,2147483647),
										array(2851995648,2852061183),
										array(2886729728,2887778303),
										array(3221225984,3221226239),
										array(3232235520,3232301055),
										array(4294967040,4294967295)
									);

		foreach (self::$reservedIPs as $r)
			if (($addr >= $r[0]) && ($addr <= $r[1])) return false;
			
		return true;
	}
	
	public static function getUniqueID() {
	    if(!self::$uniqueID)
	        self::$uniqueID = Framework::uniqueHash(self::getRemoteAddress() . ":" . $_SERVER["HTTP_USER_AGENT"], Framework::RawHash);
	        
	    return self::$uniqueID;
	}
	
	public static function getUniqueDomainID() {
	    if(!self::$uniqueDomainID)
	        self::$uniqueDomainID = Framework::uniqueHash(self::getRemoteAddress() . ":" . $_SERVER["HTTP_USER_AGENT"], Framework::RawHash);
	        
	    return self::$uniqueDomainID;
	}
	
	public static function getRemoteAddress(){
		if(!self::$remoteAddr)
			foreach(Array("HTTP_FORWARDED_FOR", "HTTP_X_FORWARDED", "HTTP_FORWARDED", "REMOTE_ADDR") as $remoteKey)
				if (isset($_SERVER[$remoteKey]) &&
					self::validateIP(self::$remoteAddr = $_SERVER[$remoteKey]))
					break;
		
		return self::$remoteAddr;
	}
	
	public static function isWindows(){
	    $ua = $_SERVER["HTTP_USER_AGENT"];
	    
	    return stripos($ua, 'windows') ? true : false;
	}
	
	public static function isLinux(){
	    $ua = $_SERVER["HTTP_USER_AGENT"];
	    
	    return stripos($ua, 'linux') ? true : false;
	}
	
	public static function isMac(){
	    $ua = $_SERVER["HTTP_USER_AGENT"];
	    
	    return stripos($ua, 'mac') ? true : false;
	}
	
	public static function htmlAddressInfo($addr =false) {
		if(!$addr)
			$addr = self::getRemoteAddress();
			
		return "<a title='IP $addr' href='http://www.infobyip.com/ip-$addr.html'>" . gethostbyaddr($addr) . "</a>";
	}

}
?>
