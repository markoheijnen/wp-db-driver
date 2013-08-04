<?php

require( dirname( __FILE__ ) . '/inc/interface-wp-db-driver.php' );

class wpdb_drivers extends wpdb {

	/**
	 * Database driver object
	 *
	 * Allow for simple, and backwards compatible, pluggable database drivers
	 * like PDO_mysql and mysqli to power wpdb
	 * 
	 * @access protected
	 * @since 3.5.0
	 * @var wpdb_driver
	 */
	protected $dbh;

 	/**
	 * Pick the adapter to be used for performing the actual queries.
	 *
	 * @since 3.6.0
	 */
	private function set_driver() {

		// Auto-pick the driver
		if ( defined( 'WPDB_DRIVER' ) ) {
			$driver = WPDB_DRIVER;
		} elseif ( extension_loaded( 'pdo_mysql' ) ) {
			$driver = 'pdo_mysql';
		} elseif ( extension_loaded( 'mysqli' ) ) {
			$driver = 'mysqli';
		} elseif ( extension_loaded( 'mysql' ) ) {
			$driver = 'mysql';
		}

		// Get the new driver
		if ( in_array( $driver, array( 'mysql', 'mysqli', 'pdo_mysql' ) ) ) {
			require_once( dirname( __FILE__ ) . '/drivers/' . $driver . '.php' );
		}
		$class = 'wpdb_driver_' . $driver;

		if ( !class_exists( $class ) ) {
			wp_load_translations_early();
			$this->bail( sprintf( __( "
				<h1>No database drivers found</h1>.
				<p>WordPress requires the mysql, mysqli, or pdo_mysql extension to talk to your database.</p>
				<p>If you're unsure what these terms mean you should probably contact your host. If you still need help you can always visit the <a href='http://wordpress.org/support/'>WordPress Support Forums</a>.</p>
			"), 'db_connect_fail' ) );
		}

		$this->dbh = new $class();
	}

	/**
	 * Connects to the database server and selects a database
	 *
	 * PHP5 style constructor for compatibility with PHP5. Does
	 * the actual setting up of the class properties and connection
	 * to the database.
	 *
	 * @link http://core.trac.wordpress.org/ticket/3354
	 * @since 2.0.8
	 *
	 * @param string $dbuser MySQL database user
	 * @param string $dbpassword MySQL database password
	 * @param string $dbname MySQL database name
	 * @param string $dbhost MySQL database host
	 */
	function __construct( $dbuser, $dbpassword, $dbname, $dbhost ) {
		$this->set_driver();

		parent::__construct( $dbuser, $dbpassword, $dbname, $dbhost );
	}

	/**
	 * Sets the connection's character set.
	 *
	 * @since 3.1.0
	 *
	 * @param resource $dbh     The resource given by mysql_connect
	 * @param string   $charset The character set (optional)
	 * @param string   $collate The collation (optional)
	 */
	function set_charset($dbh, $charset = null, $collate = null) {
		if ( ! isset( $charset ) )
			$charset = $this->charset;
		if ( ! isset( $collate ) )
			$collate = $this->collate;
		if ( $this->has_cap( 'collation', $dbh ) && !empty( $charset ) ) {
			$query = $this->prepare( 'SET NAMES %s', $charset );
			if ( ! empty( $collate ) )
				$query .= $this->prepare( ' COLLATE %s', $collate );
			$this->dbh->query( $query );
		}
	}

	/**
	 * Selects a database using the current database connection.
	 *
	 * The database name will be changed based on the current database
	 * connection. On failure, the execution will bail and display an DB error.
	 *
	 * @since 0.71
	 *
	 * @param string $db MySQL database name
	 * @param resource $dbh Optional link identifier.
	 * @return null Always null.
	 */
	function select( $db, $dbh = null ) {
		$result = $dbh->select( $db );

		if ( ! $result ) {
			$this->ready = false;
			wp_load_translations_early();
			$this->bail( sprintf( __( '<h1>Can&#8217;t select database</h1>
<p>We were able to connect to the database server (which means your username and password is okay) but not able to select the <code>%1$s</code> database.</p>
<ul>
<li>Are you sure it exists?</li>
<li>Does the user <code>%2$s</code> have permission to use the <code>%1$s</code> database?</li>
<li>On some systems the name of your database is prefixed with your username, so it would be like <code>username_%1$s</code>. Could that be the problem?</li>
</ul>
<p>If you don\'t know how to set up a database you should <strong>contact your host</strong>. If all else fails you may find help at the <a href="http://wordpress.org/support/">WordPress Support Forums</a>.</p>' ), htmlspecialchars( $db, ENT_QUOTES ), htmlspecialchars( $this->dbuser, ENT_QUOTES ) ), 'db_select_fail' );
			return;
		}
	}

	/**
	 * Real escape
	 *
	 * @since 2.8.0
	 * @access private
	 *
	 * @param  string $string to escape
	 * @return string escaped
	 */
	function _real_escape( $string ) {
		return $this->dbh->escape( $string );
	}

	/**
	 * Print SQL/DB error.
	 *
	 * @since 0.71
	 * @global array $EZSQL_ERROR Stores error information of query and error string
	 *
	 * @param string $str The error to display
	 * @return bool False if the showing of errors is disabled.
	 */
	function print_error( $str = '' ) {
		global $EZSQL_ERROR;

		if ( !$str )
			$str = $this->dbh->get_error_message();
		$EZSQL_ERROR[] = array( 'query' => $this->last_query, 'error_str' => $str );

		if ( $this->suppress_errors )
			return false;

		wp_load_translations_early();

		if ( $caller = $this->get_caller() )
			$error_str = sprintf( __( 'WordPress database error %1$s for query %2$s made by %3$s' ), $str, $this->last_query, $caller );
		else
			$error_str = sprintf( __( 'WordPress database error %1$s for query %2$s' ), $str, $this->last_query );

		error_log( $error_str );

		// Are we showing errors?
		if ( ! $this->show_errors )
			return false;

		// If there is an error then take note of it
		if ( is_multisite() ) {
			$msg = "WordPress database error: [$str]\n{$this->last_query}\n";
			if ( defined( 'ERRORLOGFILE' ) )
				error_log( $msg, 3, ERRORLOGFILE );
			if ( defined( 'DIEONDBERROR' ) )
				wp_die( $msg );
		} else {
			$str   = htmlspecialchars( $str, ENT_QUOTES );
			$query = htmlspecialchars( $this->last_query, ENT_QUOTES );

			print "<div id='error'>
			<p class='wpdberror'><strong>WordPress database error:</strong> [$str]<br />
			<code>$query</code></p>
			</div>";
		}
	}

	/**
	 * Kill cached query results.
	 *
	 * @since 0.71
	 * @return void
	 */
	function flush() {
		$this->dbh->flush();
		parent::flush();
	}

	/**
	 * Connect to and select database
	 *
	 * @since 3.0.0
	 */
	function db_connect() {
		$this->is_mysql = true;

		if ( false !== strpos( $this->dbhost, ':' ) ) {
			list( $host, $port ) = explode( ':', $this->dbhost );
 		}
 		else {
			$host = $this->dbhost;
			$port = 3306;
		}

		$options = array();
		$options['key'] = defined( 'DB_SSL_KEY' ) ? DB_SSL_KEY : null;
		$options['cert'] = defined( 'DB_SSL_CERT' ) ? DB_SSL_CERT : null;
		$options['ca'] = defined( 'DB_SSL_CA' ) ? DB_SSL_CA : null;
		$options['ca_path'] = defined( 'DB_SSL_CA_PATH' ) ? DB_SSL_CA_PATH : null;
		$options['cipher'] = defined( 'DB_SSL_CIPHER' ) ? DB_SSL_CIPHER : null;

		if ( ! $this->dbh->connect( $host, $this->dbuser, $this->dbpassword, $port, $options ) ) {
			wp_load_translations_early();
			$this->bail( sprintf( __( "
<h1>Error establishing a database connection</h1>
<p>This either means that the username and password information in your <code>wp-config.php</code> file is incorrect or we can't contact the database server at <code>%s</code>. This could mean your host's database server is down.</p>
<ul>
	<li>Are you sure you have the correct username and password?</li>
	<li>Are you sure that you have typed the correct hostname?</li>
	<li>Are you sure that the database server is running?</li>
</ul>
<p>If you're unsure what these terms mean you should probably contact your host. If you still need help you can always visit the <a href='http://wordpress.org/support/'>WordPress Support Forums</a>.</p>
" ), htmlspecialchars( $this->dbhost, ENT_QUOTES ) ), 'db_connect_fail' );

			return;
		}

		$this->set_charset( $this->dbh );

		$this->ready = true;

		$this->select( $this->dbname, $this->dbh );
	}

	/**
	 * Perform a MySQL database query, using current database connection.
	 *
	 * More information can be found on the codex page.
	 *
	 * @since 0.71
	 *
	 * @param string $query Database query
	 * @return int|false Number of rows affected/selected or false on error
	 */
	function query( $query ) {
		if ( ! $this->ready )
			return false;

		// some queries are made before the plugins have been loaded, and thus cannot be filtered with this method
		$query = apply_filters( 'query', $query );

		$return_val = 0;
		$this->flush();

		// Log how the function was called
		$this->func_call = "\$db->query(\"$query\")";

		// Keep track of the last query for debug..
		$this->last_query = $query;

		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES )
			$this->timer_start();

		$this->result = $this->dbh->query( $query );
		$this->num_queries++;

		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES )
			$this->queries[] = array( $query, $this->timer_stop(), $this->get_caller() );

		// If there is an error then take note of it..
		if ( $this->last_error = $this->dbh->get_error_message() ) {
			$this->print_error();
			return false;
		}

		if ( preg_match( '/^\s*(create|alter|truncate|drop)\s/i', $query ) ) {
			$return_val = $this->result;
		} elseif ( preg_match( '/^\s*(insert|delete|update|replace)\s/i', $query ) ) {
			$this->rows_affected = $this->dbh->affected_rows();
			// Take note of the insert_id
			if ( preg_match( '/^\s*(insert|replace)\s/i', $query ) ) {
				$this->insert_id = $this->dbh->insert_id();
			}
			// Return number of rows affected
			$return_val = $this->rows_affected;
		} else {
			$return_val = $this->num_rows = count( $this->last_result );
		}

		$this->last_result = $this->dbh->get_results();

		return $return_val;
	}

	/**
	 * Load the column metadata from the last query.
	 *
	 * @since 3.5.0
	 *
	 * @access protected
	 */
	protected function load_col_info() {
		$this->col_info = $this->dbh->load_col_info();
	}

	/**
	 * The database version number.
	 *
	 * @since 2.7.0
	 *
	 * @return false|string false on failure, version number on success
	 */
	function db_version() {
		return $this->dbh->db_version();
	}
}

$wpdb = new wpdb_drivers( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
