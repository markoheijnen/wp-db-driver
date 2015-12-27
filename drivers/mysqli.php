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
class wpdb_driver_mysqli extends wpdb_driver {

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


	public static function get_name() {
		return 'MySQLi';
	}

	public static function is_supported() {
		return extension_loaded( 'mysqli' );
	}


	/**
	 * Escape with mysql_real_escape_string()
	 *
	 * @see mysqli_real_escape_string()
	 *
	 * @param  string $string to escape
	 * @return string escaped
	 */
	public function escape( $string ) {
		return $this->dbh->real_escape_string( $string );
	}

	/**
	 * Get the latest error message from the DB driver
	 *
	 * @return string
	 */
	public function get_error_message() {
		return $this->dbh->error;
	}

	/**
	 * Free memory associated with the resultset
	 *
	 * @return void
	 */
	public function flush() {
		if ( $this->result instanceof mysqli_stmt ) {
			$this->result->free_result();
		}

		$this->result = null;
		$this->col_info = null;

		// Sanity check before using the handle
		if ( empty( $this->dbh ) || ! ( $this->dbh instanceof mysqli ) ) {
			return;
		}

		// Clear out any results from a multi-query
		while ( mysqli_more_results( $this->dbh ) ) {
			mysqli_next_result( $this->dbh );
		}
	}

	/**
	 * Check if server is still connected
	 * @return bool
	 */
	public function is_connected() {
		if ( ! $this->dbh || 2006 == $this->dbh->connect_errno ) {
			return false;
		}

		return true;
	}

	/**
	 * Connect to database
	 * @return bool
	 */
	public function connect( $host, $user, $pass, $port = 3306, $options = array() ) {
		$client_flags = defined( 'MYSQL_CLIENT_FLAGS' ) ? MYSQL_CLIENT_FLAGS : 0;

		$this->dbh = mysqli_init();

		$socket = null;
		$port_or_socket = strstr( $host, ':' );
			
		if ( ! empty( $port_or_socket ) ) {
			$host = substr( $host, 0, strpos( $host, ':' ) );
			$port_or_socket = substr( $port_or_socket, 1 );

			if ( 0 !== strpos( $port_or_socket, '/' ) ) {
				$port = intval( $port_or_socket );
				$maybe_socket = strstr( $port_or_socket, ':' );

				if ( ! empty( $maybe_socket ) ) {
					$socket = substr( $maybe_socket, 1 );
				}
			} else {
				$socket = $port_or_socket;
				$port = null;
			}
		}

		if ( WP_DEBUG ) {
			$this->dbh->real_connect( $host, $user, $pass, null, $port, $socket, $client_flags );
		} else {
			@$this->dbh->real_connect( $host, $user, $pass, null, $port, $socket, $client_flags );
		}

		if ( ! empty( $options['key'] ) && ! empty( $options['cert'] ) && ! empty( $options['ca'] ) ) {
			$this->dbh->ssl_set(
				$options['key'],
				$options['cert'],
				$options['ca'],
				$options['ca_path'],
				$options['cipher']
			);
		}

		return ( ! mysqli_connect_error() );
	}

	/**
	 * Disconnect the database connection
	 */
	public function disconnect() {
		$this->dbh->close();
		$this->dbh = null;
	}

	/**
	 * Ping a server connection or reconnect if there is no connection
	 * @return bool
	 */
	public function ping() {
		if ( ! $this->dbh ) {
			return false;
		}

		return @ $this->dbh->ping();
	}

	/**
	 * Sets the connection's character set.
	 *
	 * @param resource $dbh     The resource given by the driver
	 * @param string   $charset Optional. The character set. Default null.
	 * @param string   $collate Optional. The collation. Default null.
	 */
	public function set_charset( $charset = null, $collate = null ) {
		if ( $this->has_cap( 'collation' ) && ! empty( $charset ) ) {
			if ( function_exists( 'mysqli_set_charset' ) && $this->has_cap( 'set_charset' ) ) {
				mysqli_set_charset( $this->dbh, $charset );

				return true;
			}
		}

		return false;
	}

	/**
	 * Get the name of the current character set.
	 *
	 * @return string Returns the name of the character set
	 */
	public function connection_charset() {
		return mysqli_character_set_name( $this->dbh );
	}

	/**
	 * Select database
	 * @return void
	 */
	public function select( $db ) {
		return $this->dbh->select_db( $db );
	}

	/**
	 * Perform a MySQL database query, using current database connection.
	 * @param string $query Database query
	 * @return int|false Number of rows affected/selected or false on error
	 */
	public function query( $query ) {
		$return_val = 0;

		if ( ! $this->dbh ) {
			return false;
		}

		$this->result = $this->dbh->query( $query );

		if ( preg_match( '/^\s*(create|alter|truncate|drop)\s/i', $query ) ) {
			$return_val = $this->result;
		}
		elseif ( preg_match( '/^\s*(insert|delete|update|replace)\s/i', $query ) ) {
			$return_val = $this->affected_rows();
		}
		elseif ( preg_match( '/^\s*select\s/i', $query ) ) {
			return is_object( $this->result ) ? $this->result->num_rows : false;
		}

		return $return_val;
	}

	/**
	 * Get result data.
	 * @param int The row number from the result that's being retrieved. Row numbers start at 0.
	 * @param int The offset of the field being retrieved.
	 * @return array|false The contents of one cell from a MySQL result set on success, or false on failure.
	 */
	public function query_result( $row, $field = 0 ) {
		if( 0 == $this->result->num_rows ) {
			return false;
		}

		$this->result->data_seek( $row );
		$datarow = $this->result->fetch_array(); 
		
		return $datarow[ $field ];
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

		if( is_object( $this->result ) ) {
			while ( $row = $this->result->fetch_object() ) {
				$ret[] = $row;
			}
		}

		return $ret;
	}

	/**
	 * Load the column metadata from the last query.
	 * @return array
	 */
	public function load_col_info() {
		if ( $this->col_info ) {
			return $this->col_info;
		}

		$num_fields = $this->result->field_count;

		for ( $i = 0; $i < $num_fields; $i++ ) {
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


	/**
	 * Determine if a database supports a particular feature.
	 */
	public function has_cap( $db_cap ) {
		$db_cap = strtolower( $db_cap );

		$version = parent::has_cap( $db_cap );

		if ( $version && 'utf8mb4' === $db_cap ) {
			return version_compare( mysqli_get_client_info(), '5.5.3', '>=' );
		}

		return $version;
	}

}
