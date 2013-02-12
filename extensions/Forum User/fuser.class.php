<?php
class ForumUser {
	
	private static $database;
	private $displayName;
	private $currentTitle;
	private $user;
	
	public static function getDatabase(){
		return self::$database ? self::$database
		            : (self::$database = Database::getInstance());
	}
	
	public function printForumUserCard($profileBase="account"){
	    echo "<a href='$profileBase/";
	    echo $this->user->getUsername();
	    echo "'><table class='user-card'><tr><td class='display-picture' rowspan=\"2\"><img src='";
	    echo $this->user->getAvatar();
	    echo "' /></td><td class='username' valign='bottom'>";
	    echo $this->user->getDisplayName();
	    echo "</td></tr><tr><td valign='top' class='title'>";
	    echo $this->getTitle();
	    echo "</td></tr></table></a>";
	}
	
	public function __construct($user){
	    $this->user = $user;
	    $forumUser = self::getDatabase()->selectRow("account",
	                            Array("rowid" => $user->getID()));
	    if(!$forumUser) {
	        $forumUser = Array("rowid" => $this->user->getID(),
	                           "display-name" => $user->getUsername());
	        self::getDatabase()->insert("account", $forumUser);
	    }
	    $this->displayName = $forumUser['display-name'];
	    $this->currentTitle = $forumUser['custom-title'];
	}
	
	public function getTitle(){
	    if(!$this->currentTitle) {
	        if($this->user->getLevel() != 0)
	            $this->currentTitle = $this->user->getLevelString();
	        else
	            $this->currentTitle = self::getDatabase()->selectField("reputation-titles", Array("< req-rep" => $this->reputation), "title", "No Title", "reputation DESC");
	    }
	    return $this->currentTitle;
	}
	
	
	public function getDisplayName(){
	    return $this->displayName;
	}
	
}
?>
