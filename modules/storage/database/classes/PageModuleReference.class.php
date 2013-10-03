<?php
class PageModuleReference extends DatabaseDataPrototype {

    protected function initImpl($db){}
    
    protected function resolveImpl(){
        return new PageModule($this->getRaw());
    }

}
?>
