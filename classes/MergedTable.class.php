<?php
class MergedTable {

	private $tables = Array();
	
	public function addTable($db, $table=false){
		$dbname = get_class($db);
		if($dbname == "Database")
			array_push($this->tables, $db->$table);
		else if($dbname == "DBTable")
			array_push($this->tables, $db);
		else
			throw new Exception("Excepcted first argument to be DBTable or Database object");
	}
	
	public function _select(){
	}
	
}
?>
