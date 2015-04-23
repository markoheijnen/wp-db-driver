<?php

function wp_set_error_handler() {
	if ( defined( 'E_DEPRECATED' ) ) {
		$errcontext = E_WARNING | E_DEPRECATED;
	}
	else {
		$errcontext = E_WARNING;
	}

	set_error_handler( 'wp_error_handler', $errcontext );
}

function wp_error_handler( $errno, $errstr, $errfile ) {
	$mysql_file = dirname( dirname( __FILE__ ) ) . '/drivers/mysql.php';

	if ( $mysql_file === $errfile ) {
		return true;
	}

	if ( preg_match( '/^(mysql_[a-zA-Z0-9_]+)/', $errstr, $matches ) ) {
		_doing_it_wrong( $matches[1], 'Please talk to the database using $wpdb', '4.0' );

		return apply_filters( 'wpdb_drivers_raw_mysql_call_trigger_error', true );
	}

	return apply_filters( 'wp_error_handler', false, $errno, $errstr, $errfile );
}

wp_set_error_handler();