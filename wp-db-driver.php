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
			__( 'WP DB Driver', 'menu-test' ),
			__( 'WP DB Driver', 'menu-test' ),
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

		$loaded_pdo = $loaded_mysqli = $loaded_mysql = __( 'No' );

		if( extension_loaded( 'pdo_mysql' ) )
			$loaded_pdo = __( 'Yes' );

		if( extension_loaded( 'mysqli' ) )
			$loaded_mysqli = __( 'Yes' );

		if( extension_loaded( 'mysql' ) )
			$loaded_mysql = __( 'Yes' );


		echo '<table>';

		echo '<tr>';
		echo '<td>PDO</td>';
		echo '<td>' . $loaded_pdo . '</td>';
		echo '</tr>';


		echo '<tr>';
		echo '<td>MySQLi</td>';
		echo '<td>' . $loaded_mysqli . '</td>';
		echo '</tr>';


		echo '<tr>';
		echo '<td>MySQL</td>';
		echo '<td>' . $loaded_mysql . '</td>';
		echo '</tr>';

		echo '</div>';
	}

	public function get_current_driver() {
		$driver = false;

		if ( defined( 'WPDB_DRIVER' ) ) {
			$driver = WPDB_DRIVER;
		} elseif ( extension_loaded( 'pdo_mysql' ) ) {
			$driver = 'pdo_mysql';
		} elseif ( extension_loaded( 'mysqli' ) ) {
			$driver = 'mysqli';
		} elseif ( extension_loaded( 'mysql' ) ) {
			$driver = 'mysql';
		}

		return $driver;
	}

}

if( is_admin() )
	new WP_DB_Driver_Plugin;