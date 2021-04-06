<?php
/**
 * Pinterest Vendor API
 *
 * @author      WooCommerce
 * @category    API
 * @package     Pinterest_For_Woocommerce/API
 * @version     1.0.0
 */

namespace Automattic\WooCommerce\Pinterest\API;

use \WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VendorAPI {

	private $api_namespace = \PINTEREST_FOR_WOOCOMMERCE_API_NAMESPACE . '/v';
	private $api_version   = \PINTEREST_FOR_WOOCOMMERCE_API_VERSION;

	public $base;
	public $methods = 'POST';
	public $endpoint_callback;

	// Getters
	public function get_namespace() {
		return $this->api_namespace;
	}

	public function get_version() {
		return $this->api_version;
	}

	/**
	 * Register endpoint Routes
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {

		$namespace = $this->api_namespace . $this->api_version;

		register_rest_route(
			$namespace,
			'/' . $this->base,
			array(
				array(
					'methods'             => $this->methods,
					'callback'            => array( $this, $this->endpoint_callback ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Authenticate request
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return boolean
	 */
	public function permissions_check( WP_REST_Request $request ) {

		return true;
	}

	/**
	 * Return is user has permissions to edit option
	 *
	 * @param string $option
	 * @param WP_REST_Request $request
	 *
	 * @return boolean
	 */
	public function user_has_option_permission( $option, $request ) {

		$permissions = apply_filters( PINTEREST_FOR_WOOCOMMERCE_PREFIX . '_rest_api_option_permissions', array(), $option, $request );
		if ( isset( $permissions[ $option ] ) ) {
			return $permissions[ $option ];
		}

		return current_user_can( 'manage_options' );
	}
}
