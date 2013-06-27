<?php
/*
Plugin Name: WP DB Driver
Plugin URI: http://core.trac.wordpress.org/ticket/21663
Description: Enables PDO or MySQLi
Author: Kurt Payne and Marko Heijnen
Text Domain: wp-db-driver
Version: 1.0
Author URI: http://core.trac.wordpress.org/ticket/21663
*/

class WP_DB_Driver_Plugin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
	}

	public function add_page() {
		add_management_page(
			__( 'WP DB Driver', 'wp-db-driver' ),
			__( 'WP DB Driver', 'wp-db-driver' ),
			'manage_options',
			'wp-db-driver',
			array( $this, 'page_overview' )
		);
	}

	public function page_overview() {
		if ( ! current_user_can( 'manage_options' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );


		echo '<div class="wrap">';

		screen_icon('options-general');
		echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';

		$loaded_pdo = $loaded_mysqli = $loaded_mysql = __( 'Not installed', 'wp-db-driver' );

		if( extension_loaded( 'pdo_mysql' ) )
			$loaded_pdo = __( 'Installed', 'wp-db-driver' );

		if( extension_loaded( 'mysqli' ) )
			$loaded_mysqli = __( 'Installed', 'wp-db-driver' );

		if( extension_loaded( 'mysql' ) )
			$loaded_mysql = __( 'Installed', 'wp-db-driver' );

		echo '<div class="tool-box"><h3 class="title">' . __( 'Supported drivers', 'wp-db-driver' ) . '</h3></div>';

		echo '<table class="form-table">';
		echo '<tr>';
		echo '<th>PDO</th>';
		echo '<td>' . $loaded_pdo . '</td>';
		echo '</tr>';


		echo '<tr>';
		echo '<th>MySQLi</th>';
		echo '<td>' . $loaded_mysqli . '</td>';
		echo '</tr>';


		echo '<tr>';
		echo '<th>MySQL</th>';
		echo '<td>' . $loaded_mysql . '</td>';
		echo '</tr>';

		echo '</table>';
	}

	public function get_current_driver() {
		$driver = false;

		if ( defined( 'WPDB_DRIVER' ) )
			$driver = WPDB_DRIVER;
		elseif ( extension_loaded( 'pdo_mysql' ) )
			$driver = 'PDO';
		elseif ( extension_loaded( 'mysqli' ) )
			$driver = 'MySQLi';
		elseif ( extension_loaded( 'mysql' ) )
			$driver = 'MySQL';

		return $driver;
	}

}

if( is_admin() )
	new WP_DB_Driver_Plugin;