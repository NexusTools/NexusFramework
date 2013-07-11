<?php
class PageModuleReference extends DatabaseDataPrototype {

    protected function initImpl($db){}
    
    protected function resolveImpl(){
        return new PageModule($this->getRaw());
    }
    
    public static function preferredType(){
        return "VARCHAR(200)";
    }
    
    public static function isVirtual(){
        return false;
    }

}
?>
