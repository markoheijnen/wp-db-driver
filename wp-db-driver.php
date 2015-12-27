<?php
/*
	Plugin Name: WP DB Driver
	Description: Enables PDO or MySQLi
	Version:     2.1.0

	Plugin URI:  http://core.trac.wordpress.org/ticket/21663

	Author:      Marko Heijnen and Kurt Payne
	Author URI:  http://core.trac.wordpress.org/ticket/21663
	Donate link: https://markoheijnen.com/donate

	Text Domain: wp-db-driver
	Domain Path: /languages
	Network:     True
*/

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class WP_DB_Driver_Plugin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'network_admin_menu', array( $this, 'add_page' ) );

		add_action( 'admin_init', array( $this, 'remove_emergency_cookie' ) );
	}

	/**
	 * If the user has enabled emergency mode, then re-installs the db.php driver,
	 * then automatically disable emergency mode.
	 */
	public function remove_emergency_cookie() {
		if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'install-db-nonce' ) ) {
			setcookie( 'wp-db-driver-emergency-override', '', time() - YEAR_IN_SECONDS, '/', $_SERVER['HTTP_HOST'] );
		}
	}

	/**
	 * Try to delete the custom db.php drop-in.  This doesn't use the
	 * WP_Filesystem, because it's not available.
	 */
	public static function deactivate() {
		if ( file_exists( WP_CONTENT_DIR . '/db.php' ) ) {
			$crc1 = md5_file( dirname( __FILE__ ) . '/wp-content/db.php' );
			$crc2 = md5_file( WP_CONTENT_DIR . '/db.php' );

			if ( $crc1 === $crc2 ) {
				if ( false === @unlink( WP_CONTENT_DIR . '/db.php' ) ) {
					wp_die( __( 'Please remove the custom db.php drop-in before deactivating WP DB Driver', 'wp-db-driver' ) );
				}
			}
		}
	}

	/**
	 * Uninstall
	 */
	public static function uninstall() {
		global $wp_filesystem;

		if ( file_exists( WP_CONTENT_DIR . '/db.php' ) ) {
			$crc1 = md5_file( dirname( __FILE__ ) . '/wp-content/db.php' );
			$crc2 = md5_file( WP_CONTENT_DIR . '/db.php' );

			if ( $crc1 === $crc2 ) {
				$wp_filesystem->delete( $wp_filesystem->wp_content_dir() . '/db.php' );
			}
		}
	}

	public function add_page() {
		if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
			add_submenu_page(
				'settings.php', 
				__( 'WP DB Driver', 'wp-db-driver' ),
				__( 'WP DB Driver', 'wp-db-driver' ),
				'manage_options',
				'wp-db-driver',
				array( $this, 'page_overview' )
			);
		}
		else {
			add_management_page(
				__( 'WP DB Driver', 'wp-db-driver' ),
				__( 'WP DB Driver', 'wp-db-driver' ),
				'manage_options',
				'wp-db-driver',
				array( $this, 'page_overview' )
			);
		}
	}

	public function page_overview() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// Load if our custom wpdb wasn't loaded yet.
		require_once dirname( __FILE__ ) . '/inc/config.php';
		require_once dirname( __FILE__ ) . '/inc/interface-wp-db-driver.php';

		echo '<div class="wrap">';

		screen_icon('options-general');
		echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';

		// Don't force a specific file system method
		$method = '';

		// Define any extra pass-thru fields (none)
		$form_fields = array();

		// Define the URL to post back to (this one)
		$url = $_SERVER['REQUEST_URI'];

		// Install flags
		$do_install = ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'install-db-nonce' ) );
		$do_uninstall = ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'uninstall-db-nonce' ) );

		if ( is_super_admin() && ( $do_install || $do_uninstall ) ) {

			// Ask for credentials, if necessary
			if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {

				return true;
			} elseif ( ! WP_Filesystem($creds) ) {

				// The credentials are bad, ask again
				request_filesystem_credentials( $url, $method, true, false, $form_fields );
				return true;
			} else {
				// Once we get here, we should have credentials, do the file system operations
				global $wp_filesystem;

				// Install
				if ( $do_install ) {
					if ( $wp_filesystem->put_contents( $wp_filesystem->wp_content_dir() . '/db.php' , file_get_contents( dirname( __FILE__ ) .'/wp-content/db.php' ), FS_CHMOD_FILE ) ) {
						echo '<div class="updated"><p><strong>' . __( 'db.php has been installed.', 'wp-db-driver' ) .'</strong></p></div>';
					} else {
						echo '<div class="error"><p><strong>' . __( "db.php couldn't be installed. Please try is manually", 'wp-db-driver' ) .'</strong></p></div>';
					}

				// Remove
				} elseif ( $do_uninstall ) {
					if ( $wp_filesystem->delete( $wp_filesystem->wp_content_dir() . '/db.php' ) ) {
						echo '<div class="updated"><p><strong>' . __( 'db.php has been removed.', 'wp-db-driver' ) .'</strong></p></div>';
					} else {
						echo '<div class="error"><p><strong>' . __( "db.php couldn't be removed. Please try is manually", 'wp-db-driver' ) .'</strong></p></div>';
					}

				}
			}
		}

		echo '<div class="tool-box"><h3 class="title">' . __( 'Current driver', 'wp-db-driver' ) . '</h3></div>';

		if ( file_exists( WP_CONTENT_DIR . '/db.php' ) ) {
			$crc1 = md5_file( dirname( __FILE__ ) . '/wp-content/db.php' );
			$crc2 = md5_file( WP_CONTENT_DIR . '/db.php' );

			if ( $crc1 === $crc2 ) {
				echo '<form method="post" style="display: inline;">';
				wp_nonce_field('uninstall-db-nonce');

				echo '<p><strong>' . call_user_func( array( WP_DB_Driver_Config::get_current_driver(), 'get_name' ) ) . '</strong> &nbsp; ';

				if ( function_exists( 'mysql' ) && is_super_admin() ) {
					submit_button( __( 'Remove', 'wp-db-driver' ), 'primary', 'install-db-php', false );
				}

				echo '</p>';

				echo '</form>';

			} else {
				echo '<form method="post" style="display: inline;">';
				wp_nonce_field('install-db-nonce');

				echo '<p><strong>' . __( 'Another db.php is installed', 'wp-db-driver' ) . '</strong> &nbsp; ';

				if ( is_super_admin() ) {
					submit_button( __( 'Install', 'wp-db-driver' ), 'primary', 'install-db-php', false );
				}

				echo '</p>';

				echo '</form>';
			}
		}
		else {
			echo '<form method="post" style="display: inline;">';
			wp_nonce_field('install-db-nonce');

			echo '<p><strong>' . __( 'No custom db.php installed', 'wp-db-driver' ) . '</strong> &nbsp; ';

			if ( is_super_admin() ) {
				submit_button( __( 'Install', 'wp-db-driver' ), 'primary', 'install-db-php', false );
			}

			echo '</p>';

			echo '</form>';
		}

		echo '<div class="tool-box"><h3 class="title">' . __( 'Supported drivers', 'wp-db-driver' ) . '</h3></div>';

		echo '<table class="form-table">';

		$drivers = WP_DB_Driver_Config::get_drivers();
		foreach ( $drivers as $driver => $file ) {
			include_once $file;

			echo '<tr>';
			echo '<th>' . call_user_func( array( $driver, 'get_name' ) ) . '</th>';
			echo '<td>';

			if ( call_user_func( array( $driver, 'is_supported' ) ) ) {
				_e( 'Installed', 'wp-db-driver' );
			}
			else {
				_e( 'Not installed', 'wp-db-driver' );
			}

			echo '</td>';
			echo '</tr>';
		}

		echo '</table>';
	}

}

if ( is_admin() ) {
	$GLOBALS['wp_db_driver_plugin'] = new WP_DB_Driver_Plugin;
}

register_deactivation_hook( __FILE__, array( 'WP_DB_Driver_Plugin', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'WP_DB_Driver_Plugin', 'uninstall' ) );