<?php
abstract class BasicBuiltinUser extends UserInterface {

    protected function setLevelImpl($level){
        throw new Exception("Level of Built-in Users cannot be changed");
    }
	
	protected function setUsernameImpl($username){
	    throw new Exception("Username of Built-in Users cannot be changed");
	}
    
}
?>
