<?php

class WP_DB_Driver_Config {

	public static function get_drivers() {
		global $custom_drivers;

		$driver_folder = dirname( dirname( __FILE__ ) ) . '/drivers';

		$drivers = array(
			'wpdb_driver_pdo_mysql' => $driver_folder . '/pdo_mysql.php',
			'wpdb_driver_mysqli'    => $driver_folder . '/mysqli.php',
			'wpdb_driver_mysql'     => $driver_folder . '/mysql.php',
		);

		if ( isset( $custom_drivers ) && is_array( $custom_drivers ) ) {
			$drivers = $custom_drivers + $drivers;
		}

		return $drivers;
	}

	/**
	 * Getting the driver that is the best possible option.
	 *
	 * @return string The classname of the driver
	 */
	public static function get_current_driver() {
		$driver  = false;
		$drivers = self::get_drivers();

		if ( defined( 'WPDB_DRIVER' ) ) {
			$driver = WPDB_DRIVER;

			switch( $driver ) {
				case 'pdo_mysql':
					$driver = 'wpdb_driver_pdo_mysql';
					break;
				case 'mysqli':
					$driver = 'wpdb_driver_mysqli';
					break;
				case 'mysql':
					$driver = 'wpdb_driver_mysql';
					break;
			}

			if ( isset( $drivers[ $driver ] ) ) {
				include_once $drivers[ $driver ];
			}

			if ( self::class_is_driver_and_supported( $driver ) ) {
				return $driver;
			}
		}

		if ( defined( 'WP_USE_EXT_MYSQL' ) && WP_USE_EXT_MYSQL ) {
			$drivers = array( 'wpdb_driver_mysql' => $drivers['wpdb_driver_mysql'] ) + $drivers;
		}

		foreach ( $drivers as $class => $file ) {
			include_once $file;

			if ( self::class_is_driver_and_supported( $class ) ) {
				return $class;
			}
		}

		return false;
	}

	private static function class_is_driver_and_supported( $class ) {
		if ( class_exists( $class ) && call_user_func( array( $class, 'is_supported' ) ) ) {
			return true;
		}

		return false;
	}

}
