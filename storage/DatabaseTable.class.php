<?php
class DatabaseTable {

    

    public function __construct($db, $table){
        if(!($db instanceof Database) || !$db->isValid())
            throw new Exception("First argument must be a valid database");
    }

}
?>
