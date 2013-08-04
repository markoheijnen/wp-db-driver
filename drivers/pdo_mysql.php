<?php

/**
 * Database driver, using the PDO extension.
 *
 * @link http://php.net/manual/en/book.pdo.php
 *
 * @package WordPress
 * @subpackage Database
 * @since 3.6.0
 */
class wpdb_driver_pdo_mysql implements wpdb_driver {

	/**
	 * Database link
	 * @var PDO
	 */
	private $dbh = null;

	/**
	 * Result set
	 * @var PDOStatement
	 */
	private $result = null;

	/**
	 * Cached column info
	 * @var array|null
	 */
	private $col_info = null;

	/**
	 * Array of fetched rows.
	 * PDO doesn't have a "count rows" feature, so we have to fetch the rows
	 * up front, and cache them here
	 * @var array
	 */
	private $fetched_rows = array();

	/**
	 * Escape with mysql_real_escape_string()
	 * @param  string $string
	 * @return string
	 */
	public function escape( $string ) {
		return substr( $this->dbh->quote( $string ), 1, -1 );
	}

	/**
	 * Get the latest error message from the DB driver
	 * @return string
	 */
	public function get_error_message() {
		$error = $this->dbh->errorInfo();
		if ( isset( $error[2] ) ) {
			return $error[2];
		}
		return '';
	}

	/**
	 * Free memory associated with the resultset
	 * @return void
	 */
	public function flush() {
		if ( $this->result instanceof PDOStatement ) {
			$this->result->closeCursor();
		}
		$this->result = null;
		$this->col_info = null;
		$this->fetched_rows = array();
	}

	/**
	 * Connect to database
	 * @return bool
	 */
	public function connect( $host, $user, $pass, $port = 3306, $options = array() ) {
		$dsn = sprintf( 'mysql:host=%1$s;port=%2$d', $host, $port );

		try {
			$pdo_options = array();

			if ( ! empty( $options['key'] ) && ! empty( $options['cert'] ) && ! empty( $options['ca'] ) ) {
				$pdo_options[ PDO::MYSQL_ATTR_SSL_KEY ]    = $options['key'];
				$pdo_options[ PDO::MYSQL_ATTR_SSL_CERT ]   = $options['cert'];
				$pdo_options[ PDO::MYSQL_ATTR_SSL_CA ]     = $options['ca'];
				$pdo_options[ PDO::MYSQL_ATTR_SSL_CAPATH ] = $options['ca_path'];
				$pdo_options[ PDO::MYSQL_ATTR_SSL_CIPHER ] = $options['cipher'];

				// Cleanup empty values
				$pdo_options = array_filter( $pdo_options );
			}

			$this->dbh = new PDO( $dsn, $user, $pass, $pdo_options );
			$this->dbh->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		}
		catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Select database
	 * @return void
	 */
	public function select( $db ) {
		try {
			$this->dbh->exec( sprintf( 'USE `%s`', $db ) );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Perform a MySQL database query, using current database connection.
	 * @param string $query Database query
	 * @return int|false Number of rows affected/selected or false on error
	 */
	public function query( $query ) {
		$return_val = 0;

		try {
			$this->result = $this->dbh->query( $query );
		}
		catch ( Exception $e ) {
			if ( WP_DEBUG) {
				global $wpdb;
				error_log( "Error executing query: " . $e->getCode() . " - " . $e->getMessage() . " in query " . $query );
			}
			return false;
		}

		if ( preg_match( '/^\s*(create|alter|truncate|drop)\s/i', $query ) ) {
			$return_val = $this->result;
		}
		elseif ( preg_match( '/^\s*(insert|delete|update|replace)\s/i', $query ) ) {
			$return_val = $this->affected_rows();
		}
		elseif ( preg_match( '/^\s*select\s/i', $query ) ) {
			$this->get_results();
			return count( $this->fetched_rows );
		}

		return $return_val;
	}

	/**
	 * Get number of rows affected
	 * @return int
	 */
	public function affected_rows() {
		if ( $this->result instanceof PDOStatement ) {
			return $this->result->rowCount();
		}
		return 0;
	}

	/**
	 * Get last insert id
	 * @return int
	 */
	public function insert_id() {
		return $this->dbh->lastInsertId();
	}

	/**
	 * Get results
	 * @return array
	 */
	public function get_results() {
		if ( !empty( $this->fetched_rows ) ) {
			return $this->fetched_rows;
		}
		$this->fetched_rows = array();

		if ( !empty( $this->result ) && $this->result->rowCount() > 0 ) {
			try {
				while ( $row = $this->result->fetchObject() ) {
					$this->fetched_rows[] = $row;
				}
			} catch ( Exception $e ) {
			}
		}

		return $this->fetched_rows;
	}

	/**
	 * Load the column metadata from the last query.
	 * @return array
	 */
	public function load_col_info() {
		if ( $this->col_info )
			return $this->col_info;
		for ( $i = 0; $i < $this->result->columnCount() ; $i++ ) {
			$this->col_info[ $i ] = $this->result->fetchColumn( $i );
		}
		return $this->col_info;
	}

	/**
	 * The database version number.
	 * @return false|string false on failure, version number on success
	 */
	public function db_version() {
		return preg_replace( '/[^0-9.].*/', '', $this->dbh->getAttribute( PDO::ATTR_SERVER_VERSION ) );
	}

	/**
	 * Don't save any state.  The db wrapper should call connect() again.
	 * @return array
	 */
	public function __sleep() {
		return array();
	}
}
