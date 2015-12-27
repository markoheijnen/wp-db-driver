<?php

abstract class wpdb_driver {

	public static function get_name() {
		if ( function_exists('get_called_class') ) {
			return get_called_class();
		}
	}

	public static function is_supported() {
		return false;
	}

	/**
	 * Whether MySQL is used as the database engine.
	 *
	 * This is used when checking against the required MySQL version for WordPress.
	 * Normally, a replacement database drop-in (db.php) will skip these checks,
	 * but setting this to true will force the checks to occur.
	 *
	 * @since 3.3.0
	 * @access public
	 * @var bool
	 */
	public function is_mysql() {
		return true;
	}

	public abstract function escape( $string );
	public abstract function get_error_message();
	public abstract function flush();
	public abstract function is_connected();
	public abstract function connect( $host, $user, $pass, $port = 3306, $options = array() );
	public abstract function disconnect();
	public abstract function ping();
	public abstract function set_charset( $charset = null, $collate = null );
	public abstract function connection_charset();
	public abstract function select( $name );
	public abstract function query( $query );
	public abstract function query_result( $row, $field = 0 );
	public abstract function load_col_info();
	public abstract function db_version();
	public abstract function affected_rows();
	public abstract function insert_id();
	public abstract function get_results();

	/**
	 * Determine if a database supports a particular feature.
	 *
	 * @since 2.7.0
	 * @since 4.1.0 Support was added for the 'utf8mb4' feature.
	 *
	 * @see wpdb::db_version()
	 *
	 * @param string $db_cap The feature to check for. Accepts 'collation',
	 *                       'group_concat', 'subqueries', 'set_charset',
	 *                       or 'utf8mb4'.
	 * @return bool Whether the database feature is supported, false otherwise.
	 */
	public function has_cap( $db_cap ) {
		$version = $this->db_version();

		switch ( strtolower( $db_cap ) ) {
			case 'collation' :    // @since 2.5.0
			case 'group_concat' : // @since 2.7.0
			case 'subqueries' :   // @since 2.7.0
				return version_compare( $version, '4.1', '>=' );
			case 'set_charset' :
				return version_compare( $version, '5.0.7', '>=' );
			case 'utf8mb4' :      // @since 4.1.0
				return version_compare( $version, '5.5.3', '>=' );
		}

		return false;
	}

}
