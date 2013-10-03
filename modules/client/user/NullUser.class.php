<?php
class NullUser extends BuiltinUser {

    private static $instance = false;
    
    public static function instance(){
        return self::$instance === false ? (self::$instance = new NullUser()) : self::$instance;
    }

    protected function __construct(){
        UserInterface::__construct(-1, "[null]", "null", -1);
    }

}
?>
