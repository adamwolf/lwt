<?php

/**************************************************************
"Learning with Texts" (LWT) is released into the Public Domain.
This applies worldwide.
In case this is not legally possible, any entity is granted the
right to use this work for any purpose, without any conditions, 
unless such conditions are required by law.

Developed by J. Pierre in 2012
***************************************************************/

/**************************************************************
PHP Database Class
Database access via PDO, only mySQL and SQLite are supported
mySQL:
$db = new DB("dbname","dbserver[:port]","dbuserid","dbpassword");
SQLite:
$db = new DB("dbfilepath");
***************************************************************/

class DB {

	private $dbh;
	private $mysql;
	private $sqlite;
	private $lastInsertId;
	private $connectString;
	private $debugging;

	public function __construct($dbname, $dbserver = "", 
		$dbuserid = "", $dbpasswd = "") {

		$this->debugging = false;

		try {
			if ($dbserver != "") {
				$colonpos = strpos($dbserver, ":");
				if ($colonpos === false) {
					$this->connectString = 'mysql:host=' . $dbserver . 
					';dbname=' . $dbname;
				} else {
					$this->connectString = 'mysql:host=' . 
					substr($dbserver,0,$colonpos) . 
					';port=' . substr($dbserver,$colonpos+1) .  
					';dbname=' . $dbname;
				}
				$this->dbh = new PDO($this->connectString, $dbuserid, $dbpasswd, 
					array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				$this->mysql = TRUE;
				$this->sqlite = FALSE;
			}
			else {
				$this->connectString = 'sqlite:' . $dbname;
				$this->dbh = new PDO($this->connectString);
				$this->mysql = FALSE;
				$this->sqlite = TRUE;
			}
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			unset($this->lastInsertId);
		}

		catch(PDOException $e) {
			die("Connection failed: " . $e->getMessage());
		}

	}
	
	public function set_debugging($d) {

		$this->debugging = $d;

	}

	public function connect_string() {

		return $this->connectString;

	}

	public function is_sqlite() {

		return $this->sqlite;

	}

	public function is_mysql() {

		return $this->mysql;

	}

	public function exec_query($sql, $params=array()) {
	
		if ($this->debugging) self::write_sql_log("exec_query: $sql");
		
		try {
			$stmt = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->execute($params);
			$result = $stmt->fetchAll();
			$stmt->closeCursor();
			unset($this->lastInsertId);
			return $result;   // array of assoc. arrays, empty if no records found
		}
		catch(PDOException $e) {
			die("Query failed: " . $e->getMessage() . " [" . $sql . "]");
		}
		
	}

	public function exec_query_num_array($sql, $params=array()) {
		
		if ($this->debugging) self::write_sql_log("exec_query_num_array: $sql");

		try {
			$stmt = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$stmt->setFetchMode(PDO::FETCH_NUM);
			$stmt->execute($params);
			$result = $stmt->fetchAll();
			$stmt->closeCursor();
			unset($this->lastInsertId);
			return $result;   // array of num. arrays, empty if no records found
		}
		catch(PDOException $e) {
			die("Query failed: " . $e->getMessage() . " [" . $sql . "]");
		}
		
	}

	public function exec_query_onlyfirst($sql, $params=array()) {
		
		if ($this->debugging) self::write_sql_log("exec_query_onlyfirst: $sql");

		try {
			$stmt = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->execute($params);
			$result = $stmt->fetch();
			$stmt->closeCursor();
			unset($this->lastInsertId);
			return $result;    // assoc. array, or === FALSE, if no record found 
		}
		catch(PDOException $e) {
			die("Query failed: " . $e->getMessage() . " [" . $sql . "]");
		}
		
	}

	public function exec_query_value($sql, $params=array()) {
		
		if ($this->debugging) self::write_sql_log("exec_query_value: $sql");

		try {
			$stmt = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->execute($params);
			$result = $stmt->fetch();
			$stmt->closeCursor();
			unset($this->lastInsertId);
			$r = NULL;
			if ($result !== FALSE) {
				if(isset($result['value'])) $r = $result['value'];
			}
			return $r;    // one value, or NULL, if no 'value' found 
		}
		catch(PDOException $e) {
			die("Query failed: " . $e->getMessage() . " [" . $sql . "]");
		}
		
	}

	public function exec_sql($sql, $params=array(), $errdie=TRUE) {
		
		if ($this->debugging) self::write_sql_log("exec_sql: $sql");

		try {
			$stmt = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$stmt->execute($params);
			$count = $stmt->rowCount();
			$this->lastInsertId = $this->dbh->lastInsertId();
			$stmt->closeCursor();
			return $count;    // number of recs affected
		}
		catch(PDOException $e) {
			if ($errdie) die("Exec SQL failed: " . $e->getMessage() . " [" . $sql . "]");
		}
		
	}

	public function exec_sql_simple($sql) {
		
		if ($this->debugging) self::write_sql_log("exec_sql_simple: $sql");

		try {
			$this->dbh->exec($sql);
			return 0;   
		}
		catch(PDOException $e) {
			return 1;
		}
		
	}

	public function begin_transaction() {
		
		if ($this->debugging) self::write_sql_log("begin_transaction");

		try {
			return $this->dbh->beginTransaction();
		}
		catch(PDOException $e) {
			die("Begin Transaction failed: " . $e->getMessage() . " [" . $sql . "]");
		}
		
	}

	public function commit_transaction() {
		
		if ($this->debugging) self::write_sql_log("commit_transaction");

		try {
			return $this->dbh->commit();
		}
		catch(PDOException $e) {
			die("Commit Transaction failed: " . $e->getMessage() . " [" . $sql . "]");
		}
		
	}

	public function rollback_transaction() {
		
		if ($this->debugging) self::write_sql_log("rollback_transaction");

		try {
			return $this->dbh->rollBack();
		}
		catch(PDOException $e) {
			die("Rollback Transaction failed: " . $e->getMessage() . " [" . $sql . "]");
		}
		
	}

	public function quote_string($s) {
		
		return $this->dbh->quote($s);
		
	}

	public function last_insert_id() {
		
		return $this->lastInsertId;
		
	}

	private static function write_sql_log($s) {
		file_put_contents ("_sql.log", 
			date("r") . " - " . $s . "\n", 
			FILE_APPEND);
	}

}

?>