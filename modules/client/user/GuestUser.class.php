<?php
class GuestUser extends BuiltinUser {

    private static $instance = false;
    
    public function instance(){
        return self::$instance === false ? (self::$instance = new GuestUser()) : self::$instance;
    }

    protected function __construct(){
        UserInterface::__construct(-2, "[unset]", "guest");
    }

}
?>
