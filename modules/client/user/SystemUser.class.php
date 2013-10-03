<?php
class SystemUser extends BuiltinUser {

    private static $instance = false;
    
    public static function instance(){
        return self::$instance === false ? (self::$instance = new SystemUser()) : self::$instance;
    }

    protected function __construct(){
        UserInterface::__construct(-3, "", "system", 5);
    }

}
?>
