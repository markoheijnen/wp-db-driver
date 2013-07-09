<?php

if ( isset( $_GET['wp-db-driver-emergency-override'] ) ) {
	setcookie( 'wp-db-driver-emergency-override', 1, 0, '/', $_SERVER['HTTP_HOST'] );
}

if (
	! isset( $_COOKIE['wp-db-driver-emergency-override'] ) &&
	! isset( $_REQUEST['wp-db-driver-emergency-override'] ) &&
	is_file( WP_CONTENT_DIR . '/plugins/wp-db-driver/db.php' ) )
{
	require( WP_CONTENT_DIR . '/plugins/wp-db-driver/db.php' );
}
