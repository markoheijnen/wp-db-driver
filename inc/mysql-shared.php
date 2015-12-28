<?php

abstract class wpdb_driver_mysql_shared extends wpdb_driver {

	/**
	 * A list of incompatible SQL modes.
	 *
	 * @since 3.9.0
	 * @access protected
	 * @var array
	 */
	protected $incompatible_modes = array( 'NO_ZERO_DATE', 'ONLY_FULL_GROUP_BY',
		'STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES', 'TRADITIONAL' );


	/**
	 * Sets the connection's character set.
	 *
	 * @since 3.1.0
	 */
	public function set_charset( $charset = null, $collate = null ) {
		if ( $this->has_cap( 'collation' ) && ! empty( $charset ) ) {
			$query = $this->prepare( 'SET NAMES %s', $charset );

			if ( ! empty( $collate ) ) {
				$query .= $this->prepare( ' COLLATE %s', $collate );
			}

			$this->query( $query );
		}
	}

	/**
	 * Change the current SQL mode, and ensure its WordPress compatibility.
	 *
	 * If no modes are passed, it will ensure the current MySQL server
	 * modes are compatible.
	 *
	 * @since 3.9.0
	 *
	 * @param array $modes Optional. A list of SQL modes to set.
	 */
	public function set_sql_mode( $modes = array() ) {
		if ( empty( $modes ) ) {
			$res = $this->query( 'SELECT @@SESSION.sql_mode;' );

			if ( ! $res ) {
				return;
			}

			$modes_str = $this->query_result( 0 );

			if ( empty( $modes_str ) ) {
				return;
			}

			$modes = explode( ',', $modes_str );
		}

		$modes = array_change_key_case( $modes, CASE_UPPER );

		/**
		 * Filter the list of incompatible SQL modes to exclude.
		 *
		 * @since 3.9.0
		 *
		 * @param array $incompatible_modes An array of incompatible modes.
		 */
		$incompatible_modes = (array) apply_filters( 'incompatible_sql_modes', $this->incompatible_modes );

		foreach ( $modes as $i => $mode ) {
			if ( in_array( $mode, $incompatible_modes ) ) {
				unset( $modes[ $i ] );
			}
		}

		$modes_str = implode( ',', $modes );

		$this->query( "SET SESSION sql_mode='$modes_str';" );
	}

}