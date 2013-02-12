<?php
class Password extends DatabaseDataPrototype {

    protected function initImpl($db){}
    
    protected function resolveImpl(){
        return "[password]";
    }
    
    public static function preferredType(){
        return "BINARY(16)";
    }
    
    public static function isVirtual(){
        return false;
    }
    
    public function __toString(){
        return "[password]";
    }

}
?>
