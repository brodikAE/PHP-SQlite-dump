<?

/*
	sqlite database dump
	written by brodikAE
	v.0.0.1
*/

class sqlite_dump {

	function dump_sqlite($_db){
			
		$db = new SQLite3($_db);
		$db->busyTimeout(30000);

		// create common begin sql
		$sql = "";
		$sql .= "PRAGMA foreign_keys=OFF;\n";
		$sql .= "BEGIN TRANSACTION;\n";

		// create tables sql
		$tables = $db->query("SELECT name FROM sqlite_master WHERE type ='table' AND name NOT LIKE 'sqlite_%';");
		while ($table = $tables->fetchArray(SQLITE3_NUM)) {
			$sql .= $db->querySingle("SELECT sql FROM sqlite_master WHERE name = '{$table[0]}'").";";
			$rows = $db->query("SELECT * FROM {$table[0]}");
			$insert_sql = "INSERT INTO \"{$table[0]}\" ";
			$columns = $db->query("PRAGMA table_info({$table[0]})");
			$fieldnames = array();
			while($column = $columns->fetchArray(SQLITE3_ASSOC)){
				$fieldnames[] = $column["name"];
			}
			$insert_sql .= "VALUES";
			while ($row=$rows->fetchArray(SQLITE3_ASSOC)) {
				foreach ($row as $k => $v) {
					if(is_int($v)){
						$val = $db->escapeString($v);
						$row[$k] = $val;
					}elseif(is_null($v)){
						$row[$k] = "NULL";
					}else{
						//solve some issues in my scripts
						$double_chars['in'] = array("\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe");
						$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
						$v = str_replace($double_chars['in'], $double_chars['out'], $v);
						$val = $db->escapeString($v);
						$row[$k] = "'".$val."'";
					}
				}
				$sql .= "\n".$insert_sql."(".implode(",",$row).");";
			}
			$sql = rtrim($sql, ";").";\n";
		}

		// create sqlite_sequence sql
		$sql .= "DELETE FROM sqlite_sequence;";
		$sql_sequence = $db->query("SELECT name FROM sqlite_master WHERE name = 'sqlite_sequence';");
		$table = $sql_sequence->fetchArray(SQLITE3_NUM);
		$rows = $db->query("SELECT * FROM {$table[0]}");
		$insert_sql = "INSERT INTO \"{$table[0]}\" ";
		$columns = $db->query("PRAGMA table_info({$table[0]})");
		$fieldnames = array();
		while($column = $columns->fetchArray(SQLITE3_ASSOC)){
			$fieldnames[] = $column["name"];
		}
		$insert_sql .= "VALUES";
		while ($row=$rows->fetchArray(SQLITE3_ASSOC)) {
			foreach ($row as $k => $v) {
				if(is_int($v)){
					$val = $db->escapeString($v);
					$row[$k] = $val;
				}elseif(is_null($v)){
					$row[$k] = "NULL";
				}else{
					//solve some issues
					$double_chars['in'] = array("\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe");
					$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
					$v = str_replace($double_chars['in'], $double_chars['out'], $v);
					$val = $db->escapeString($v);
					$row[$k] = "'".$val."'";
				}
			}
			$sql .= "\n".$insert_sql."(".implode(",",$row).");";
		}
		$sql = rtrim($sql, ";").";\n";

		// create non sqlite index sql
		$tables = $db->query("SELECT name FROM sqlite_master WHERE type ='index' AND name NOT LIKE 'sqlite_autoindex%';");
		while ($table = $tables->fetchArray(SQLITE3_NUM)) {
			$sql .= $db->querySingle("SELECT sql FROM sqlite_master WHERE name = '{$table[0]}'").";"."\n";
		}

		// create view and trigger sql
		$tables = $db->query("SELECT name FROM sqlite_master WHERE type ='view' OR type ='trigger';");
		while ($table = $tables->fetchArray(SQLITE3_NUM)) {
			$sql .= $db->querySingle("SELECT sql FROM sqlite_master WHERE name = '{$table[0]}'").";"."\n";
		}

		// create common end sql
		$sql .= "COMMIT;";

		return $sql;

	}

}

$sqlite_dump = new sqlite_dump();

?>
