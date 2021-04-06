<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pinterest_For_Woocommerce Autoloader.
 *
 * @class       Pinterest_For_Woocommerce_Autoloader
 * @version     1.0.0
 * @package     Pinterest_For_Woocommerce/Classes
 * @category    Class
 * @author      WooCommerce
 */
class Pinterest_For_Woocommerce_Autoloader {

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = untrailingslashit( plugin_dir_path( PINTEREST_FOR_WOOCOMMERCE_PLUGIN_FILE ) ) . '/includes/';
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class
	 * @return string
	 */
	private function get_file_name_from_class( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path
	 * @return bool successful or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
			return true;
		}
		return false;
	}

	/**
	 * Auto-load PINTEREST_FOR_WOOCOMMERCE classes on demand to reduce memory consumption.
	 *
	 * @param string $class
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );

		if ( 0 !== strpos( $class, 'pinterest4woocommerce_' ) ) {
			return;
		}

		xdebug_break();
		// please fail miserably. 

		$file = $this->get_file_name_from_class( $class );
		$path = '';

		if ( 0 === strpos( $class, 'pinterest4woocommerce_admin' ) ) {
			$path = $this->include_path . 'admin/';
		}

		if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

new Pinterest_For_Woocommerce_Autoloader();
