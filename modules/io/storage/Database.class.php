<?php

class Database extends Lockable {

    private static $logFile = false;
	private static $instances = Array();
	private static $knownInstances = Array();
	private static $knownKeywords = Array("CURRENT_TIMESTAMP", "CURRENT_DATE", "CURRENT_TIME");
	private static $queries = 0;
	private $activetransaction = false;
	private $databaseDefinition = false;
	private $shutdownCommands = Array();
	private $instanceName;
	private $database;
	private $defPath;
	private $dbPath;
	private $lastException;
	private $lastError;
	private $lastQuery;
	
	/*
	
	Returns full definition if table is null
	Returns raw json data when table === true
	
	*/
	public function getDefinition($table=null){
	    if($this->databaseDefinition === false)
	        try {
	        	if(!is_file($this->defPath))
	        		throw new Exception("Definition Missing");
	            $this->databaseDefinition = json_decode(trim(file_get_contents($this->defPath)), true);
	            if(!$this->databaseDefinition)
	                throw new Exception("Bad Data");
	        } catch(Exception $e) {
	            $this->databaseDefinition = Array();
	        }
	
	    if($table === true)
	        return json_encode($this->databaseDefinition);
	
	    if(is_string($table)) {
	        if(!array_key_exists($table, $this->databaseDefinition))
	            return Array();
	            
	        $fieldDefs = array_key_exists("fields", $this->databaseDefinition[$table]) ?
	                        $this->databaseDefinition[$table]['fields']
	                      : $this->databaseDefinition[$table];
	                      
	        foreach($fieldDefs as &$keyDef){
	            if(is_string($keyDef) && startsWith($keyDef, ":")) {
	                $keyDef = self::resolvePrototype(substr($keyDef, 1));
                    $keyDef = call_user_func(Array($keyDef, "preferredType"));
	            } else if(is_array($keyDef) && startsWith($keyDef['type'], ":")) {
                    $keyDef['type'] = self::resolvePrototype(substr($keyDef['type'], 1));
                    $keyDef['type'] = call_user_func(Array($keyDef['type'], "preferredType"));
                }
	        }
	        
	        return $fieldDefs;
	    } else
	        return $this->databaseDefinition;
	}
	
	public function beginTransaction() {
		if(defined("ERROR_OCCURED"))
			throw new Exception("Cannot write to database in error state");
		if(!$this->activetransaction)
			$this->activetransaction = $this->database->beginTransaction();
		return $this->activetransaction;
	}
	
	public function commit() {
		if(!$this->activetransaction)
			return false;
		$this->activetransaction = false;
		return $this->database->commit();
	}
	
	public function rollBack() {
		if(!$this->activetransaction)
			return false;
		$this->activetransaction = false;
		return $this->database->rollBack();
	}
	
	public function lastError(){
	    return $this->lastError;
	}
	
	public function lastException(){
	    return $this->lastException;
	}
	
	public function lastQuery(){
	    return $this->lastQuery;
	}
	
	public function _getName(){
	    return $this->instanceName;
	}
	
	public static function timestampToTime($stamp){
		if($stamp == "1970-01-01 00:00:00")
			return 0;
		return strtotime("$stamp GMT");
	}
	
	public static function timeToTimestamp($time){
		return gmdate("Y-m-d H:i:s", $time);
	}
	
	public static function countQueries(){
		return self::$queries;
	}
	
	public function _isValid(){
		return $this->database != null;
	}
	
	public function _getFilePath(){
		return $this->dbPath;
	}
	
	private static function setupFieldsDefinition(&$tblFields){
	    if(!array_key_exists("created", $tblFields))
            $tblFields['created'] = Array("type" => "TIMESTAMP", "default" => "CURRENT_TIMESTAMP");
        if(!array_key_exists("created-by", $tblFields))
            $tblFields['created-by'] = Array("type" => "INTEGER",
                                                "default" => "{{User::getID()}}",
                                                "class" => "UserReference");
        if(!array_key_exists("modified", $tblFields))
            $tblFields['modified'] = Array("type" => "TIMESTAMP",
                                        "default" => "CURRENT_TIMESTAMP",
                                        "update" => "CURRENT_TIMESTAMP");
        if(!array_key_exists("modified-by", $tblFields))
            $tblFields['modified-by'] = Array("type" => "INTEGER",
                                                "default" => "{{User::getID()}}",
                                                "update" => "{{User::getID()}}",
                                                "class" => "UserReference");
	}
	
	public function _processDefinition($def){
        if(!$this->database || !is_array($def))
            throw new Exception("Invalid Database or Definition Given");

        foreach($def as &$tblDef)
            if(array_key_exists("fields", $tblDef))
                self::setupFieldsDefinition($tblDef["fields"]);
            else
                self::setupFieldsDefinition($tblDef);
        
	    $cDef = $this->getDefinition();
	    $this->databaseDefinition = $def;
	    
        foreach($def as $name => &$tblDef)
            if(!isset($cDef[$name]) || strcmp(Framework::uniqueHash($cDef[$name], Framework::RawHash), Framework::uniqueHash($tblDef, Framework::RawHash)) != 0)
                if(!$this->_createOrUpgradeTable($name, $tblDef))
                    throw new Exception("Failed to Create or Upgrade Table $name", false, $this->lastException);
                    
        
        if(!file_put_contents($this->defPath, json_encode($def)))
            throw new Exception("Failed to Store Definition");
	}
	
	public function _exec($query){
	    self::$queries++;
		if(($ret = $this->database->exec($query)) === false) {
			$this->lastQuery = $query;
			$this->lastError = $this->database->errorInfo();
			$this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()) . "\n" . $query);
			if(DEBUG_MODE)
				throw $this->lastException;
			return false;
		}
		
		return $ret;
	}
	
	public function _selectRow($table, $where, $fields=false, $orderBy=false, $assoc=true){
		$res = $this->_select($table, $where, $fields, 1, false, $orderBy, $assoc);
		if($res && count($res))
			return $res[0];
		else
			return false;
	}
	
	public function _selectFields($table, $field, $where=false, $limit=false, $to=false, $orderBy=false){
		$data = $this->_select($table, $where, Array($field), $limit, $to, $orderBy, false);
		if($data) {
		    $list = Array();
		    foreach($data as $res)
		        array_push($list, $res[0]);
		    return $list;
		} else
			return false;
	}
	
	public function _selectField($table, $where, $field, $default=false, $orderBy=false, $resolveProtoValue=false){
		$res = $this->_select($table, $where, Array($field), 1, false, $orderBy, false);
		if($res && array_key_exists(0, $res) &&
				array_key_exists(0, $res[0])) {
			$value = $res[0][0];
		    if($resolveProtoValue === true && $value instanceof DatabaseDataPrototype)
		        return $value->resolve();
		        
			return $value;
		} else
			return $default;
	}
	
	public function _queryRows($table, $where, $start, $limit, $orderBy=false, $fields=false){
		$results = Array();
		$results['total'] = $this->_countRows($table, $where);
		$results['results'] = $this->_select($table, $where, $fields, $start, $limit, $orderBy);
		return $results;
	}
	
	public function _countRows($table, $where=false){
		$res = $this->_select($table, $where, "count(*)", false, false, false, false);
		if($res && count($res))
			return $res[0][0];
	}
	
	private static function whereClause($where, &$args){
		if(is_array($where)){
		    if(!count($where))
		        return "";
			$queryString = " WHERE ";
			$lastWasOr = false;
			$first=true;
			foreach($where as $key => $val) {
				if($val === "OR") {
					$lastWasOr = true;
					continue;
				}
			
				if($first)
					$first = false;
				else if($lastWasOr) {
					$queryString .= " OR ";
					$lastWasOr = false;
				} else
					$queryString .= " AND ";
				
				$parts = explode(' ', $key);
				if(count($parts) == 1) {
					if(preg_match("/^\w+\(`?\w+`?\)$/", $key) || startsWith($key, '`'))
						$queryString .= "$key=?";
					else
						$queryString .= "`$key`=?";
				    array_push($args, $val);
				} else if(count($parts) == 2) {
					$queryString .= "`$parts[1]` $parts[0] ?";
					array_push($args, $val);
				} else
					throw new Exception("Too many parts for where attribute `$key` " . count($parts));
			}
				
			return $queryString;
		} else if(is_string($where))
			return " WHERE $where";
	}
	
	public function _incrementField($table, $where, $field, $by=1){
		$args = Array();
		$queryString = "UPDATE `$table` SET `$field` = `$field` + $by";
		$queryString .= self::whereClause($where, $args);
		
		$statement = $this->database->prepare($queryString);
		if(!$statement || ($ret = $statement->execute($args)) === false) {
			$this->lastError = $this->database->errorInfo();
			$this->lastQuery = $queryString;
			$this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()));
			if(DEBUG_MODE)
				throw $this->lastException;
		    return false;
		}
		
		self::$queries++;
		return $ret;
	}
	
	public function _decrementField($table, $where, $field, $by=1){
		$args = Array();
		$queryString = "UPDATE `$table` SET `$field` = `$field` - $by";
		$queryString .= self::whereClause($where, $args);
		
		$statement = $this->database->prepare($queryString);
		if(!$statement || ($ret = $statement->execute($args)) === false) {
			$this->lastError = $this->database->errorInfo();
			$this->lastQuery = $queryString;
			$this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()));
			if(DEBUG_MODE)
				throw $this->lastException;
		    return false;
		}
		
		self::$queries++;
		return $ret;
	}
	
	public function _selectDistinctValues($table, $distinctField, $where=false){
	    $args = Array();	    
	    $values = Array();
	    $queryString = "SELECT DISTINCT `$distinctField` FROM `$table`";
	    if($where)
	        $queryString .= self::whereClause($where, $args);
	    $statement = $this->database->prepare($queryString);
		if(!$statement || $statement->execute($args) === false) {
			$this->lastError = $this->database->errorInfo();
			$this->lastQuery = $queryString;
			$this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()));
			if(DEBUG_MODE)
				throw $this->lastException;
		    return false;
		}
		
		self::$queries++;
		foreach($statement->fetchAll(PDO::FETCH_NUM) as $value){
			array_push($values, $value[0]);
		}
		return $values;
	}
	
	public function _select($table, $where=false, $fields=false, $limit=false, $to=false, $orderBy=false, $assoc=true, $resolveProtoClasses=false){
		$args = Array();
		$queryString = "SELECT ";
		$shiftRowID = false;
		if($fields) {
			if(is_array($fields)){
			    if($resolveProtoClasses && !in_array("rowid", $fields)) {
			        array_unshift($fields, "rowid");
			        $shiftRowID = true;
			    } else
			        $resolveProtoClasses = false;
			
				$first = true;
				foreach($fields as $field) {
					if($first)
						$first = false;
					else
						$queryString .= ",";
					
					if(!startsWith($queryString, '`'))
						$queryString .= "`$field`";
					else
						$queryString .= $field;
				}
			} else {
			    $resolveProtoClasses = false;
				$queryString .= "$fields";
		    }
		} else
			$queryString .= "rowid,*";
		
		$queryString .= " FROM `$table`";
		if($where)
			$queryString .= self::whereClause($where, $args);
		if($orderBy) {
			if(is_string($orderBy))
				$orderBy = explode(" ", $orderBy);
			if(count($orderBy) == 2)
				$queryString .= " ORDER BY `$orderBy[0]` " . strtoupper($orderBy[1]);
			else
				$queryString .= " ORDER BY `$orderBy[0]`";
		}
		if($limit !== false) {
			$queryString .= " LIMIT $limit";
			if($to !== false)
				$queryString .= ",$to";
		}
		$statement = $this->database->prepare($queryString);
		if(!$statement || $statement->execute($args) === false) {
			$this->lastError = $this->database->errorInfo();
			$this->lastQuery = $queryString;
			$this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()));
			if(DEBUG_MODE)
				throw $this->lastException;
		    return false;
		}
		
		self::$queries++;
	    
	    
	    if($resolveProtoClasses === true) {
	        $data = $statement->fetchAll($assoc ? PDO::FETCH_ASSOC : PDO::FETCH_NUM);
	        $def = $this->getDefinition($table);
            foreach($data as &$row){
                if($assoc) {
                    foreach($row as $field => &$entry) {
                        if($field == "rowid")
                            continue; // Skip ROWID
                        $this->resolveEntryDataClass($entry, $def[$field], $row['rowid'], $table);
                    }
                    if($shiftRowID)
                        unset($row['rowid']);
                } else {
                    for($i=1; $i<count($fields); $i++)
                        $this->resolveEntryDataClass($row[$i], $def[$fields[$i]], $row[0], $table);
                        
                    if($shiftRowID)
                        array_shift($row);
                }
            }
            return $data;
        } else
            return $statement->fetchAll($assoc ? PDO::FETCH_ASSOC : PDO::FETCH_NUM);
        
	}
	
	protected static function resolvePrototype($name){
	    $path = dirname(__FILE__) . DIRSEP . "classes" . DIRSEP . "$name.class.php";
	    if(!is_readable($path))
	        throw new Exception("Failed to load prototype for `$name`");
	    require_once($path);
	}
	
	protected static function resolveEntryDataClass(&$entry, $fieldDef, $rowId, $table){
	    if(!is_array($fieldDef) || !array_key_exists("class", $fieldDef))
	        return;
	    
	    self::resolvePrototype($fieldDef['class']);
	    $dataProto = new $fieldDef['class']();
	    if(!($dataProto instanceof DatabaseDataPrototype))
	        throw new Exception("Class Specified Not a Valid DatabaseDataPrototype Class");
	    $dataProto->init($entry, $rowid, $table, $this);
	    
	    $entry = $dataProto;
	}
	
	public function _listColumns($table, $includeExtensions=false){
	    // PRAGMA table_info(`access`)
	    $statement = $this->database->prepare("PRAGMA table_info(`$table`)");
	    if(!$statement || $statement->execute() === false)
	        return false;
	    $columns = Array("rowid");
	    $data = $statement->fetchAll(PDO::FETCH_ASSOC);
	    foreach($data as $row)
	        array_push($columns, $row['name']);
	    return $columns;
	}
	
	public function _listTables(){
	    $statement = $this->database->prepare("SELECT name FROM SQLITE_MASTER WHERE type='table'");
	    if(!$statement || $statement->execute() === false)
	        return false;
	    $tables = Array();
	    $data = $statement->fetchAll(PDO::FETCH_NUM);
	    foreach($data as $row)
	        array_push($tables, $row[0]);
	    return $tables;
	}
	
	public function _renameTable($oldName, $newName) {
		return $this->_exec("ALTER TABLE `$oldName` RENAME TO `$newName`") !== false;
	}
	
	public function _dropTable($name){
	    if($this->_exec("DROP TABLE `$name`") === false) {
	        $this->lastError = $this->database->errorInfo();
	        
	        $this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()));
	        return false;
	    }
	    
		return true;
	}
	
	public function _delete($table, $where=Array()){
		$args = Array();
		$deleteQuery = "DELETE FROM `$table`" . self::whereClause($where, $args);
		$statement = $this->database->prepare($deleteQuery);
		if(!$statement || $statement->execute($args) === false) {
			$this->lastError = $this->database->errorInfo();
			$this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()));
		    return false;
		}
		
		Triggers::broadcast("Database", $this->getName() . ".$table", Array("delete", array_keys($where)));
		return true;
	}
	
	public static function writeLog($content){
	    if(!$content || DEBUG_MODE)
	        return;
	        
	    if(!self::$logFile) {
	        if(!is_dir($basePath = INDEX_PATH . "debug"))
		        mkdir($basePath, 0777, true);
		    self::$logFile = fopen($basePath . DIRSEP . str_replace("/", "_", REQUEST_URI) . ".database.txt", "a");
		    fwrite(self::$logFile, "NexusFramework Database Debugger\n" . date(DATE_RFC822) . "\n----------------\n\n");
	        
	    }
	    fwrite(self::$logFile, $content);
	}
	
	public function _insert($table, $values) {
		$this->beginTransaction();
		$query = "INSERT INTO `$table` (";
		
		// Process defaults
		$tblDef = $this->getDefinition($table);
		foreach($tblDef as $field => $def)
		    if(is_array($def) && array_key_exists('default', $def)
		            && startsWith($def['default'], "{{")
		            && !array_key_exists($field, $values))
		        $values[$field] = interpolate($def['default'], true);
		
		ob_start("Database::writeLog");
		echo "Inserting Data\n";
		print_r($values);
		       
		$first = true;
		foreach(array_keys($values) as $key) {
			if($first)
				$first = false;
			else
				$query .= ",";
			$query .= "`$key`";
		}
		
		$query .= ") VALUES (";
		$first = true;
		$stringValues = Array();
		foreach(array_values($values) as $value) {
			if($first)
				$first = false;
			else
				$query .= ",";
				
	        if(is_bool($value))
	            $query .= $value ? "1" : "0";
	        else if(is_numeric($value))
	            $query .= $value;
	        else {
	            if(in_array($value, self::$knownKeywords))
	                $query .= $value;
	            else if(strlen($value)) {
			        $query .= "?";
			        array_push($stringValues, $value);
			    } else
			        $query .= "\"\"";
			}
			
			
		}
		$query .= ")";
		
		echo "Query: $query\n";
		print_r($stringValues);
		ob_end_flush();
		
		$statement = $this->database->prepare($query);
		if(!$statement || $statement->execute($stringValues) === false) {
			$this->lastError = $this->database->errorInfo();
			$this->lastQuery = $query;
			$this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()) . "\n" . $query);
		    return false;
		}
		
		$rowid = intval($this->database->lastInsertId());
		Triggers::broadcast("Database", $this->getName() . ".$table", Array("insert", $rowid, array_keys($values)));
	    return $rowid;
	}
	
	public function _selectRecursive($table, $parent=0, $checkConditions=false, $where=false, $parentField="parent", $idField="rowid", $useClassBackings=true){
		if($where)
			$cwhere = array_merge(Array($parentField => $parent), $where);
		else
			$cwhere = Array($parentField => $parent);
		
		$entries = $this->select($table, $cwhere);
		if(!is_array($entries))
			return false;

		if($checkConditions) {
			$passedEntries = Array();
			foreach($entries as &$entry)
				if(Framework::testCondition($entry['condition']))
					array_push($passedEntries, $entry);
			
			$entries = $passedEntries;
		}
		
		foreach($entries as &$entry)
			$entry['children'] = $this->selectRecursive($table, $entry[$idField], $checkConditions, $where, $parentField, $idField);
		return $entries;
	}
	
	public function _selectArray($table, $where, $field){
		$array = Array();
		foreach($this->_select($table, $where, Array($field), false, false, false, false) as $entry)
			array_push($array, $entry[0]);
		return $array;
	}
	
	public function __shutdown(){
		try {
			if(defined("ERROR_OCCURED"))
				throw new Exception("Cannot commit changes in error state");
			foreach($this->shutdownCommands as $command)
				call_user_func_array(Array($this, array_shift($command)), $command);
			$this->commit();
		}catch(Exception $e) {
			$this->rollBack();
		}
		
	    try {
	        $this->unlock();
	    }catch(Exception $e){}
	}
	
	public function _toggleField($table, $field, $where=false, $now=true){
	    $this->_update($table, Array($field => "NOT `$field`"), $where, $now);
	}
	
	public function _upsert($table, $values, $where=false) {
		if(is_numeric($where)) // allow passing where as rowid
			$where = Array("rowid" => $where);
		
		$count = $this->update($table, $values, $where);
		if($count === false)
			return false;
		if($count > 0)
			return true;
		if($where)
			$values = array_merge($values, $where);
		return $this->insert($table, $values);
	}
	
	public function _update($table, $values, $where=false) {
		$this->beginTransaction();
		if(is_numeric($where)) // allow passing where as rowid
			$where = Array("rowid" => $where);
			
	    ob_start("Database::writeLog");
		echo "Updating Data\n";
		print_r($values);
		
		$args = Array();
		$first = true;
		$updateQuery = "UPDATE `$table` SET ";
		
		$tblDef = $this->getDefinition($table);
		foreach($tblDef as $field => $def)
		    if(is_array($def) && array_key_exists('update', $def)
		            && !array_key_exists($field, $values))
		        $values[$field] = interpolate($def['update'], true);
		
		echo "Modified Values\n";
		print_r($values);
		
		foreach($values as $key => $value){
			if($first)
				$first = false;
			else
				$updateQuery .= ",";

			if(startsWith($value, "NOT ") || in_array($value, self::$knownKeywords))
				$updateQuery .= "`$key` = $value";
			else {
				array_push($args, $value);
				$updateQuery .= "`$key`=?";
			}
		}
		
		if($where)
			$updateQuery .= self::whereClause($where, $args);
		
		echo "Query: $updateQuery\n";
		print_r($args);
		ob_end_flush();
		
		$statement = $this->database->prepare($updateQuery);
		if(!$statement || ($ret = $statement->execute($args)) === false) {
			$this->lastError = $this->database->errorInfo();
			$this->lastQuery = $updateQuery;
			$this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()));
			return false;
		}
		
		Triggers::broadcast("Database", $this->getName() . ".$table", Array("insert", $where ? array_keys($where) : Array()));
		return $statement->rowCount();
	}
	
	public function _updateTable($name, $def){
		// TODO: Finish Updating Table Definitions
		//$this->_renameTable($name, "__$name");
	}
	
	protected function _createOrUpgradeTable($name, $def){
		$tableFields = isset($def['fields']) ? $def['fields'] : $def;
		$dataToAdd = false;
		try {
		    $rawFields = $this->listColumns($name);
		    if(!$rawFields)
		        throw new Exception("No Existing Fields");
		    
		    $existingFields = Array("rowid");
		    foreach($rawFields as $field)
		        if(array_key_exists($field, $tableFields))
		            $existingFields[] = $field;
            
		    if(count($existingFields) <= 1)  // Essentially a new table...
		        throw new Exception("No Existing Fields");
		        
		    $dataToAdd = $this->select($name, false, $existingFields);
		} catch(Exception $e) {}
		if(!is_array($dataToAdd)) {
		    $dataToAdd = isset($tableFields['default-values']) ? $tableFields['default-values'] : false;
		    if(is_assoc($dataToAdd))
		        $dataToAdd = Array($dataToAdd);
		} else if(!$this->dropTable($name))
		    throw new Exception("Failed to Drop Existing Table", 0, $this->lastException);
	    
		$unique = Array();
		$createQuery = "CREATE TABLE `$name` (";
		
		$args = Array();
		$first = true;
		foreach($tableFields as $key => $keyDef){
			if($key == "default-values")
				continue;
		    
			if($first)
				$first = false;
			else
				$createQuery .= ",";
				
			if(is_array($keyDef)) {
				if(isset($keyDef['primary']))
					array_push($unique, $key);
                
                if(startsWith($keyDef['type'], ":")) {
                    $keyDef['type'] = self::resolvePrototype(substr($keyDef['type'], 1));
                    $keyDef['type'] = call_user_func(Array($keyDef['type'], "preferredType"));
                }
                    
                $createQuery .= "\"$key\" $keyDef[type]";
				if(isset($keyDef['default'])) {
					$default = $keyDef['default'];
					if(is_bool($default))
						$default = $default ? "TRUE" : "FALSE";
					else if(startsWith($default, "{{"))
					    $default = false;
					else if(!is_numeric($default) && !in_array($default, self::$knownKeywords))
					        $default = "\"" . addslashes($default) . "\"";
                    
					if($default)
					    $createQuery .= " DEFAULT $default";
				}
				
				if(isset($keyDef['case-insensative']))
				    $createQuery .= " COLLATE NOCASE";
			} else {
			    if(startsWith($keyDef, ":")) {
			        self::resolvePrototype($keyDef = substr($keyDef, 1));
                    $keyDef = call_user_func(Array($keyDef, "preferredType"));
                }
				$createQuery .= "\"$key\" $keyDef";
			}
		}
		
		if(count($unique)){
			$createQuery .= ",UNIQUE(";
			$first = true;
			foreach($unique as $key){
				if($first)
					$first = false;
				else
					$createQuery .= ",";
				$createQuery .= "`$key`";
			}
			$createQuery .= ")";
		}
		
		$createQuery .= ")";
		
		if($this->_exec($createQuery) === false) {
		    $this->lastError = $this->database->errorInfo();
		    $this->lastQuery = $createQuery;
			$this->lastException = new Exception("DatabaseError: " . json_encode($this->database->errorInfo()) . "\n" . $createQuery);
			return false;
	    }
		
		if(is_array($dataToAdd) && count($dataToAdd)) {
			foreach($dataToAdd as $dataRow)
				if($this->_insert($name, $dataRow) === false)
				    throw new Exception("Failed to insert old data\n" . json_encode($this->lastError) . "\n" . $this->lastQuery);
	    }
		return true;
	}
	
	private function __construct($extension, $def, $name="database"){
	    $basePath = Framework::getConfigFolder($extension);
	    Lockable::init("$basePath$name.lock");
	    
	    $this->lock();
	    try {
		    $this->instanceName = $extension;
		    
		    $this->dbPath = "$basePath$name.sqlite";
		    $this->defPath = "$basePath$name.json";
		    
		    // Attempt Upgrade
		    if($name == "database" && !is_file($this->dbPath)
		                    && is_file(CONFIG_PATH . "$extension.definition.json")
		                    && is_file(CONFIG_PATH . "$extension.sqlite")) {
		        if(!copy(CONFIG_PATH . "$extension.definition.json", $this->defPath)
		                || !copy(CONFIG_PATH . "$extension.sqlite", $this->dbPath))
		            throw new Exception("Failed to upgrade database `$extension/$name` from legacy format");
		        unlink(CONFIG_PATH . "$extension.definition.json");
		        unlink(CONFIG_PATH . "$extension.sqlite");
		    }
		    
		    $this->database = new PDO("sqlite:" . $this->dbPath);
		    if($def)
		        $this->_processDefinition($def);
            
		    register_shutdown_function(Array($this, "__shutdown"));
		}catch(Exception $e){
		    $this->unlock();
		    throw $e;
		}
		$this->unlock();
	}
	
	public static function getInstance($extension = null, $definition = null, $name = "database"){
		if(!$extension) { // Detect Name from Paths
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$callingFile = null;
			foreach($backtrace as $frame) {
				if(!isset($frame['file']) ||
						(startsWith($frame['file'], FRAMEWORK_PATH) &&
						!startsWith($frame['file'], FRAMEWORK_EXT_PATH)))
					continue; // Skip Frames in Framework

				$callingFile = $frame['file'];
				break;
			}
			
			if(!$callingFile)
				throw new Exception("getInstance called from unhandlable source...");
			
			foreach(self::$knownInstances as $key => $def) {
				if(startsWith($callingFile, $def))
					$extension = $key;
			}
			
			if(!$extension)
				throw new Exception("No Registered Database for `$extension`");
		}
		
		$database = relativepath($extension . DIRSEP . $name);
		if(!isset(self::$instances[$database]))
			self::$instances[$database] = new Database($extension, $definition, $name);
		
		return self::$instances[$database];
	}
	
	
	public static function registerInstance($name, $path){
		self::$knownInstances[$name] = $path;
	}
	
	public static function processDefinition($definition, $database){
	    self::getInstance($database, $definition);
	}
	
	public function __call($name, $args){
		$method = false;
		try {
			$method = new ReflectionMethod($this, "_$name");
		} catch(Exception $e) {}
		
		if($method)
			return $method->invokeArgs($this, $args);
		
		throw new Exception("Call to undefined method Database::$name()");
	}
	
	public static function __callStatic($name, $args){
		return self::getInstance()->__call($name, $args);
	}

}

?>
