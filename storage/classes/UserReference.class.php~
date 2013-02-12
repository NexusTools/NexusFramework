<?php
class UserReference extends DatabaseDataPrototype {

    protected function initImpl($db){}
    
    protected function resolveImpl(){
        return User::fetch($this->getRaw(), User::FETCH_ANY_USER);
    }
    
    public static function preferredType(){
        return "INTEGER";
    }
    
    public static function isVirtual(){
        return false;
    }

}
?>
