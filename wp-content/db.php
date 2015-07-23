<?php

if ( isset( $_GET['wp-db-driver-emergency-override'] ) ) {
	setcookie( 'wp-db-driver-emergency-override', 1, 0, '/', $_SERVER['HTTP_HOST'] );
}

$db_plugin_file = WP_CONTENT_DIR . '/plugins/wp-db-driver/inc/db-driver.php';
if ( defined( 'WP_PLUGIN_DIR' ) ) {
	$db_plugin_file = WP_PLUGIN_DIR . '/wp-db-driver/inc/db-driver.php';
}

if (
	! isset( $_COOKIE['wp-db-driver-emergency-override'] ) &&
	! isset( $_REQUEST['wp-db-driver-emergency-override'] ) &&
	is_file( $db_plugin_file ) )
{
	require( $db_plugin_file );
}
