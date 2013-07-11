<?php
abstract class DatabaseDataPrototype {

    private $raw;
    private $rowid= -1;
    private $table;
    private $resolveData;
    
    public function init($raw, $id, $table, $db){
        if($this->rowid > -1)
            return; // Ignore Additional Attempts
        $this->rowid = $id;
        $this->raw = $raw;
        $this->table = $table;
        $this->initImpl($db);
    }
    
    public function resolve(){
        if(!isset($this->resolveData))
            $this->resolveData = $this->resolveImpl();
            
        return $this->resolveData;
    }
    
    protected abstract function resolveImpl(); // Return a resolved version
    protected abstract function initImpl($db);
    //public static abstract function preferredType(); // The database backing
    //public static abstract function isVirtual(); // Whether or not this field should actually exist in the database

    public function getRaw(){
        return $this->raw;
    }
    
    public function getRowID(){
        return $this->rowid;
    }
    
    public function getTable(){
        return $this->table;
    }
    
    public function __toString(){
        return $this->raw;
    }
    
}
?>
