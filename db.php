<?php
/**
 *
 *	db.php￼
 *	Created on 21.05.2013.
 *
 *	@author Daniel Rufus Kaldheim <daniel@kaldheim.org>
 *	@copyright 2010 - 2013 Crudus Media
 *	@package CCMS/Core
 *	@version 1.0
 *	@todo Lag ny database klasse med støtte for mysql og mysqli
 *
 */
class db {

	var $lastError;		// Holds the last error
	var $lastQuery;		// Holds the last query
	var $result;		// Holds the MySQL query result
	var $records;		// Holds the total number of records returned
	var $affected;		// Holds the total number of records affected
	var $rawResults;	// Holds raw 'arrayed' results
	var $arrayedResult;	// Holds an array of the result


	private $hostname;	// MySQL Hostname
	private $username;	// MySQL Username
	private $password;	// MySQL Password
	private $database;	// MySQL Database
	private $charset;	// MySQL Charset
	private $collate;	// MySQL Collate

	var $databaseLink;		// Database Connection Link

	/**
	 * Class construction
	 * @param string $database defaults to DB_NAVN
	 * @param string $username defaults to DB_BRUKER
	 * @param string $password defaults to DB_PASSORD
	 * @param string $hostname defaults to DB_HOST
	 * @param string $charset  defaults to DB_CHARSET
	 * @param string $collate  defaults to DB_COLLATE
	 */
	function __construct($database = DB_NAVN, $username = DB_BRUKER, $password = DB_PASSORD, $hostname = DB_HOST, $charset = DB_CHARSET, $collate = DB_COLLATE) {
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;
		$this->hostname = $hostname;
		$this->charset  = $charset;
		$this->collate  = $collate;

		$this->Connect();
	}
	/**
	 * Creates connection
	 * @param boolean $persistant Use persistant connection?
	 * @return boolean
	 */
	private function Connect($persistant = false) {
		if ($this->databaseLink){
			mysql_close($this->databaseLink);
		}
		if ($persistant) {
			$this->databaseLink = mysql_pconnect($this->hostname, $this->username, $this->password);
		}
		else {
			$this->databaseLink = mysql_connect($this->hostname, $this->username, $this->password);
		}

		if (!$this->databaseLink) {
   			$this->lastError = 'Could not connect to server: ' . mysql_error($this->databaseLink);
			return false;
		}

		mysql_set_charset($this->charset, $this->databaseLink);
		mysql_query("SET NAMES ".$this->charset, $this->databaseLink);

		if (!$this->UseDB()) {
			$this->lastError = 'Could not connect to database: ' . mysql_error($this->databaseLink);
			return false;
		}
		return true;
	}

	/**
	 * Connects class to database
	 * @return boolean
	 */
	private function UseDB() {
		if (!mysql_select_db($this->database, $this->databaseLink)) {
			$this->lastError = 'Cannot select database: ' . mysql_error($this->databaseLink);
			return false;
		}
		else {
			return true;
		}
	}


	/**
	 * Performs a 'mysql_real_escape_string' on the entire array/string
	 * @param mixed $data
	 * @return mixed
	 */
	public function SecureData($data) {
		if (is_array($data)) {
			foreach ($data as $key => $val) {
				if (!is_array($data[$key])) {
					$data[$key] = mysql_real_escape_string($data[$key], $this->databaseLink);
				}
			}
		}
		else {
			$data = mysql_real_escape_string($data, $this->databaseLink);
		}
		return $data;
	}


	/**
	 * Executes MySQL query
	 * @param string $query
	 */
	public function ExecuteSQL($query) {
		$this->lastQuery 	= $query;
		if ($this->result 	= mysql_query($query, $this->databaseLink)) {
			$this->records 	= @mysql_num_rows($this->result);
			$this->affected	= @mysql_affected_rows($this->databaseLink);

			if ($this->records > 0) {
				$this->ArrayResults();
				return $this->arrayedResult;
			}
			else {
				return true;
			}
		}
		else {
			$this->lastError = mysql_error($this->databaseLink);
			return false;
		}
	}

	/**
	 * Insert into table
	 * @param array $vars    array('key' => 'value');
	 * @param string $table
	 * @param array  $exclude
	 * @return boolean
	 */
	public function Insert(array $vars, $table, $exclude = array()) {
		$vars = $this->SecureData($vars);

		$query = "INSERT INTO `{$table}` SET ";
		$values = array();
		foreach ($vars as $key => $value) {
			if (in_array($key, $exclude)) {
				continue;
			}
			$values[] = "`{$key}` = '{$value}'";
		}
		$query .= implode(', ', $values);
		return $this->ExecuteSQL($query);
	}

	/**
	 * Delete from table
	 * @param string  $table
	 * @param array   $where
	 * @param string  $limit
	 * @param boolean $like
	 * @return boolean
	 */
	public function Delete($table, array $where, $limit = null, $like = false) {
		$query = "DELETE FROM `{$table}` WHERE ";
		if (is_array($where) && !empty($where)) {
			$where = $this->SecureData($where);
			$values = array();
			foreach ($where as $key => $value){
				if ($like) {
					$values[] = "`{$key}` LIKE '%{$value}%'";
				}
				else {
					$values[] = "`{$key}` = '{$value}'";
				}
			}
			$query .= implode(' AND ', $values);
		}
		if (isset($limit)) {
			$query .= ' LIMIT ' . $limit;
		}
		return $this->ExecuteSQL($query);
	}

	/**
	 * Select from table
	 * @param string|array	$fields
	 * @param string  		$table
	 * @param array   		$where 		array('key' => 'value'); | array(array('key' => 'field_name', 'like' => false,  'value' => 'field_value'), array('operand' => 'and', 'key' => 'field_name', 'like' => false, 'value' => 'field_value')); | array(array('query' => '`field_name` = 'escaped field value'), array('query' => 'OR `field_name` = REGEX '[[:<:]]".$rid."[[:>:]]'));
	 * @param string 		$orderBy
	 * @param string  		$limit
	 * @param boolean 		$like
	 * @param string  		$operand
	 * @return boolean|array
	 */
	public function Select($fields = '*', $table, $where = array(), $orderBy = null, $limit = null, $like = false, $operand = 'AND', $returnArray = false) {
		if (empty($table)) {
			return false;
		}
		if (is_array($fields)) {
			foreach ($fields as $k => $f) {
				$fields[$k] = "`{$f}`";
			}
		}
		else {
			$fields = array($fields);
		}

		$query = "SELECT ".implode(',', $fields)." FROM `{$table}` ";
		if (is_array($where) && !empty($where)) {
			$query .= " WHERE ";
			$values = array();
			if (cArray::isAssoc($where)) {
				$where = $this->SecureData($where);
				foreach ($where as $key => $value) {
					if ($like) {
						$values[] = "`{$key}` LIKE '%{$value}%'";
					}
					else {
						$values[] = "`{$key}` = '{$value}'";
					}
				}
				$query .= implode(" {$operand} ", $values);
			}
			else {
				foreach ($where as $w) {
					if (isset($w['query'])) {
						$values[] = $w['query'];
					}
					else {
						$values[] = ((isset($w['operand'])) ? $w['operand'] : '')." `{$w['key']}` ".((isset($w['like'])) ? $w['like'] : '=')." '".$this->SecureData($w['value'])."'";
					}
				}
				$query .= implode(' ', $values);
			}
		}
		if (isset($orderBy)) {
			$query .= ' ORDER BY ' . $orderBy;
		}
		if (isset($limit)) {
			$query .= ' LIMIT ' . $limit;
		}

		if ($returnArray) {
			$result = $this->ExecuteSQL($query);
			if ($this->records == 1) {
				return array($result);
			}
			elseif (is_bool($result)) {
				return array();
			}
		}
		return $this->ExecuteSQL($query);
	}

	/**
	 * Update table
	 * @param string $table
	 * @param array  $set
	 * @param array  $where
	 * @param array  $exclude
	 * @return boolean
	 */
	public function Update($table, array $set, array $where, $exclude = array(), $limit = null) {
		if (empty($table)) {
			return false;
		}
		$set 	= $this->SecureData($set);
		$where 	= $this->SecureData($where);

		$query 	= "UPDATE `{$table}` SET ";
		$values = array();
		foreach ($set as $key => $value) {
			if(in_array($key, $exclude)) {
				continue;
			}
			$values[] = "`{$key}` = '{$value}'";
		}
		$query .= implode(', ', $values);

		if (!empty($where)) {
			$query .= ' WHERE ';
			$values = array();
			foreach ($where as $key => $value){
				$values[] = "`{$key}` = '{$value}'";
			}
			$query .= implode(' AND ', $values);
		}
		if (isset($limit)) {
			$query .= ' LIMIT ' . $limit;
		}
		return $this->ExecuteSQL($query);
	}

	/**
	 * Get last inserted id
	 * @return int
	 */
	public function lastInsertId() {
		return mysql_insert_id($this->databaseLink);
	}

	/**
	 * Get a single result
	 * @return array
	 */
	public function ArrayResult() {
		$this->arrayedResult = mysql_fetch_assoc($this->result);
		return $this->arrayedResult;
	}

	/**
	 * Get multiple results
	 * @return array
	 */
	public function ArrayResults() {
		if ($this->records == 1) {
			return $this->ArrayResult();
		}
		$this->arrayedResult = array();
		while ($data = mysql_fetch_assoc($this->result)) {
			$this->arrayedResult[] = $data;
		}
		return $this->arrayedResult;
	}

	/**
	 * Get multiple results by key
	 * @param string $key
	 * @return array
	 */
	public function ArrayResultsWithKey ($key = 'id') {
		if(isset($this->arrayedResult)){
			unset($this->arrayedResult);
		}
		$this->arrayedResult = array();
		while($row = mysql_fetch_assoc($this->result)){
			foreach($row as $theKey => $theValue){
				$this->arrayedResult[$row[$key]][$theKey] = $theValue;
			}
		}
		return $this->arrayedResult;
	}

	/**
	 * Create a new database
	 * @param string $databaseName
	 * @return boolean
	 */
	public function CreateDatabase($databaseName) {
		if (!isset($databaseName)) {
			return false;
		}
		return $this->ExecuteSQL("CREATE DATABASE `{$databaseName}` DEFAULT CHARACTER SET {$this->charset} DEFAULT COLLATE {$this->collate};");
	}

	/**
	 * Drop database
	 * @param string $databaseName
	 * @return boolean
	 */
	public function DropDatabase($databaseName) {
		if (!isset($databaseName)) {
			return false;
		}
		return $this->ExecuteSQL("DROP DATABASE {$databaseName}");
	}

	/**
	 * Get fields from a table
	 * @param string $table
	 * @return boolean|array
	 */
	public function GetFields($table) {
		return $this->ExecuteSQL("SHOW FIELDS FROM `{$table}`");
	}

	/**
	 * Add or change fields in table
	 * @param string $table
	 * @param array  $fields
	 * @return boolean
	 */
	public function AddFields($table, array $fields) {
		if (empty($table) && empty($fields)) {
			return false;
		}
		$fields_exists = array();
		foreach ($this->GetFields($table) as $f) {
			$fields_exists[$f['Field']] = $f;
		}

		$query = "ALTER TABLE `{$table}`";

		$values = array();
		foreach ($fields as $key => $val) {
			if (is_array($val)) {
				if (array_key_exists($val['name'], $fields_exists)) {
					if ($fields_exists[$val['name']]['Type'] != $val['type']) {
						$values[] = "CHANGE `{$val['name']}` `{$val['name']}` {$val['type']} NOT NULL".((!empty($val['default'])) ? ' DEFAULT '.$val['default'] : '');
					}
				}
				else {
					$values[] = "ADD `{$val['name']}` {$val['type']} NOT NULL".((!empty($val['default'])) ? ' DEFAULT '.$val['default'] : '');
				}
			}
			else {
				if ($val == "timestamp") {
					$default = "'0000-00-00 00:00:00'";
				}
				if (array_key_exists($key, $fields_exists)) {
					if ($fields_exists[$key]['Type'] != $val) {
						$values[] = "CHANGE `{$key}` `{$key}` {$val} NOT NULL".((!empty($default)) ? ' DEFAULT '.$default : '');
					}
				}
				else {
					$values[] = "ADD `{$key}` {$val} NOT NULL".((!empty($default)) ? ' DEFAULT '.$default : '');
				}
			}
		}
		$query .= implode(', ', $values);
		return $this->ExecuteSQL($query);
	}

	/**
	 * Drop fields
	 * @param string $table
	 * @param array  $fields
	 * @return boolean
	 */
	public function DropFields($table, array $fields) {
		if (empty($table) && empty($fields)) {
			return false;
		}
		$fields_exists = array();
		foreach ($this->GetFields($table) as $f) {
			$fields_exists[] = $f['Field'];
		}

		$query = "ALTER TABLE `{$table}`";
		$values = array();
		foreach ($fields as $field) {
			if (in_array($field, $fields_exists)) {
				$values[] = "DROP `{$field}`";
			}
		}

		$query .= implode(', ', $values);
		return $this->ExecuteSQL($query);
	}
	/**
	 * Add fulltext search key to table fields
	 * @param string $table
	 * @param array  $fields
	 */
	public function AddFullText($table, array $fields) {
		if (empty($table) && empty($fields)) {
			return false;
		}
		$fields_exists = array();
		$exfi = $this->GetFields($table);
		if (is_array($exfi)) {
			foreach ($exfi as $f) {
				$fields_exists[] = $f['Field'];
			}
		}
		$query = "ALTER TABLE `{$table}`";
		$values = array();
		foreach ($fields as $field) {
			if (in_array($field, $fields_exists)) {
				$values[] = "`{$field}`";
			}
		}
		$query .= " ADD FULLTEXT (".implode(', ', $values).")";
		return $this->ExecuteSQL($query);
	}
	/**
	 * Change tables engine. Example to MYISAM to support fulltext.
	 * @param string $table
	 * @param string $engine default: MYISAM
	 */
	public function ChangeTableEngine($table, $engine = "MyISAM") {
		if (!empty($table) && !empty($engine)) {
			return false;
		}
		$query = "ALTER TABLE `{$table}` ENGINE = {$engine}";
		return $this->ExecuteSQL($query);
	}
	/**
	 * Add table
	 * @param string 	$table
	 * @param array  	$fields
	 * @param boolean 	$ifNotExists
	 * @param string 	$engine
	 * @return boolean|array
	 */
	public function AddTable($table, array $fields, $ifNotExists = true, $engine = null) {
		$query = "CREATE TABLE".(($ifNotExists) ? ' IF NOT EXISTS' : '')." `{$table}` ";
		$values = array();
		$values[] = "`id` INT(11) NOT NULL auto_increment";
		$checkFields = array();
		foreach ($fields as $key => $val) {
			if (is_array($val)) {
				$values[] = "`{$val['name']}` {$val['type']}".((isset($val['not_null']) && $val['not_null'] === FALSE) ? '' : ' NOT NULL').((!empty($val['default'])) ? ' DEFAULT '.$val['default'] : '');
				$checkFields[] = $val['name'];
			}
			else {
				if ($val == "timestamp") {
					$default = "'0000-00-00 00:00:00'";
				}
				$values[] = "`{$key}` {$val} NOT NULL".((!empty($default)) ? ' DEFAULT '.$default : '');
				$checkFields[] = $key;
			}
		}
		if (!in_array('created', $checkFields)) {
			$values[] = "`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
		}
		if (!in_array('updated', $checkFields)) {
			$values[] = "`updated` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'";
		}

		$values[] = "PRIMARY KEY (`id`)";
		$query .= "(".implode(", ",$values).")";
		if (isset($engine)) {
			$query .= " ENGINE = {$engine} ";
		}
		$query .= " CHARACTER SET {$this->charset} COLLATE {$this->collate}";

		return $this->ExecuteSQL($query);
	}

	/**
	 * Check if table exists
	 * @param string $table
	 * @return boolean
	 */
	public function TableExists($table) {
		if (mysql_select_db('information_schema', $this->databaseLink)) {
			$q = mysql_query("SELECT COUNT(*) AS count FROM `tables` WHERE table_schema = '{$this->database}' AND table_name = '{$table}'");
			$result = mysql_result($q, 0);
			mysql_select_db($this->database, $this->databaseLink);
			return $result;
		}
		return 0;
	}

	/**
	 * Drop table
	 * @param string  $table
	 * @param boolean $ifNotExists
	 * @return boolean
	 */
	public function DropTable($table, $ifExists = true) {
		return $this->ExecuteSQL("DROP TABLE ".(($ifExists) ? ' IF EXISTS' : '')." `{$table}`");
	}

	/**
	 * Truncate table
	 * @param string $table
	 * @return boolean
	 */
	public function TruncateTable($table) {
		return $this->ExecuteSQL("TRUNCATE TABLE `{$table}`");
	}

	/**
	 * Add mysql user
	 * @param string $username
	 * @param string $password
	 * @param array  $domains  	array("%", "localhost", "127.0.0.1")
	 * @return boolean
	 */
	public function AddUser($username, $password, $domains = array("%", "localhost", "127.0.0.1")) {
		foreach ($domains as $domain) {
			if ($this->ExecuteSQL("CREATE USER '{$username}'@'{$domain}' IDENTIFIED BY '{$password}'")) {
				if ($this->ExecuteSQL("GRANT USAGE ON * . * TO '{$username}'@'{$domain}' IDENTIFIED BY '{$password}' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0")) {
					if ($this->ExecuteSQL("GRANT ALL PRIVILEGES ON `{$username}\_%` . * TO '{$username}'@'{$domain}'")) {
						if ($this->ExecuteSQL("SET PASSWORD FOR '{$username}'@'{$domain}' = PASSWORD('{$password}')")) {

						}
						else {
							return false;
						}
					}
					else {
						return false;
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		return true;
	}

	/**
	 * Delete User
	 * @param string $username
	 * @param array  $domains	array("%", "localhost", "127.0.0.1")
	 * @return boolean
	 */
	public function DeleteUser($username, $domains = array("%", "localhost", "127.0.0.1")) {
		foreach ($domains as $domain) {
			if (!$this->ExecuteSQL("DROP USER '{$username}'@'{$domain}'")) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get users grants
	 * @param  string $user default CURRENT_USER
	 * @return array
	 */
	public function GetUserGrants($user = "CURRENT_USER") {
		$grants = mysql_query("SHOW GRANTS FOR {$user}");
		$row = mysql_fetch_row($grants);
		if (preg_match("/GRANT ([\w\s,]+?) ON/im", $row[0], $result)) {
			return explode(", ", $result[1]);
		}
		return false;
	}
}


?>
