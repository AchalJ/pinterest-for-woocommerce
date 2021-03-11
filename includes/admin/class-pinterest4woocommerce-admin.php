<?php
/**
 * WordPress Plugin Boilerplate Admin
 *
 * @class    Pinterest4WooCommerce_Admin
 * @author   WooCommece
 * @category Admin
 * @package  Pinterest_For_Woocommerce/Admin
 * @version  2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Pinterest4WooCommerce_Admin class.
 */
class Pinterest4WooCommerce_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->includes();
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once 'pinterest-for-woocommerce-admin-functions.php';
		include_once 'class-pinterest4woocommerce-admin-assets.php';
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		switch ( $screen->id ) {
			case 'dashboard':
			case 'options-permalink':
			case 'users':
			case 'user':
			case 'profile':
			case 'user-edit':
		}
	}
}

return new Pinterest4WooCommerce_Admin();
