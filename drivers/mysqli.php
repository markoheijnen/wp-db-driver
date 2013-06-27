<?php

/**
 * Database driver, using the mysqli extension.
 *
 * @link http://php.net/manual/en/book.mysqli.php
 *
 * @package WordPress
 * @subpackage Database
 * @since 3.6.0
 */
class wpdb_driver_mysqli implements wpdb_driver {

	/**
	 * Database link
	 * @var mysqli
	 */
	private $dbh = null;

	/**
	 * Result set
	 * @var mysqli_stmt|mysqli_result
	 */
	private $result = null;

	/**
	 * Cached column info
	 * @var array|null
	 */
	private $col_info = null;

	/**
	 * Escape with mysql_real_escape_string()
	 * @param  string $string
	 * @return string
	 */
	public function escape( $string ) {
		return $this->dbh->escape_string( $string );
	}

	/**
	 * Get the latest error message from the DB driver
	 * @return string
	 */
	public function get_error_message() {
		return $this->dbh->error;
	}

	/**
	 * Free memory associated with the resultset
	 * @return void
	 */
	public function flush() {
		if ( $this->result instanceof mysqli_stmt ) {
			$this->result->free_result();
		}
		$this->result = null;
		$this->col_info = null;
	}

	/**
	 * Connect to database
	 * @return bool
	 */
	public function connect( $host, $user, $pass, $port = 3306 ) {
		$this->dbh = new mysqli( $host, $user, $pass, '', $port );
		return ( !mysqli_connect_error() );
	}

	/**
	 * Select database
	 * @return void
	 */
	public function select( $db ) {
		$this->dbh->select_db( $db );
	}

	/**
	 * Perform a MySQL database query, using current database connection.
	 * @param string $query Database query
	 * @return int|false Number of rows affected/selected or false on error
	 */
	public function query( $query ) {
		$this->result = $this->dbh->query( $query );
		if ( preg_match( '/^\s*(create|alter|truncate|drop)\s/i', $query ) ) {
			$return_val = $this->result;
		} elseif ( preg_match( '/^\s*(insert|delete|update|replace)\s/i', $query ) ) {
			$return_val = $this->affected_rows();
		} elseif ( preg_match( '/^\s*select\s/i', $query ) ) {
			return $this->result->num_rows;
		}
		return true;
	}

	/**
	 * Get number of rows affected
	 * @return int
	 */
	public function affected_rows() {
		return $this->dbh->affected_rows;
	}

	/**
	 * Get last insert id
	 * @return int
	 */
	public function insert_id() {
		return $this->dbh->insert_id;
	}

	/**
	 * Get results
	 * @return array
	 */
	public function get_results() {
		$ret = array();
		while ( $row = $this->result->fetch_object() ) {
			$ret[] = $row;
		}
		return $ret;
	}

	/**
	 * Load the column metadata from the last query.
	 * @return array
	 */
	public function load_col_info() {
		if ( $this->col_info )
			return $this->col_info;
		for ( $i = 0; $i < $this->result->field_count ; $i++ ) {
			$this->col_info[ $i ] = $this->result->fetch_field_direct( $i );
		}
		return $this->col_info;
	}

	/**
	 * The database version number.
	 * @return false|string false on failure, version number on success
	 */
	public function db_version() {
		return preg_replace( '/[^0-9.].*/', '', $this->dbh->server_version );
	}
}
