<?php
/**
 * Parse & return the Pinterest Feed issues
 *
 * @package     Pinterest_For_Woocommerce/API
 * @version     1.0.0s
 */

namespace Automattic\WooCommerce\Pinterest\API;

use \WP_REST_Server;
use \WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Endpoint returning the product-level issues of the XML Feed.
 */
class FeedIssues extends VendorAPI {

	/**
	 * Array used to hold the cached filenames for the feed Issues files.
	 *
	 * @var array
	 */
	private $feed_data_files = array();

	/**
	 * Initialize class
	 */
	public function __construct() {

		$this->base              = 'feed_issues';
		$this->endpoint_callback = 'get_feed_issues';
		$this->methods           = WP_REST_Server::READABLE;
		$this->feed_data_files   = Pinterest_For_Woocommerce()::get_setting( 'feed_data_cache' );
		$this->feed_data_files   = $this->feed_data_files ? $this->feed_data_files : array();

		$this->register_routes();
	}


	/**
	 * Authenticate request
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return boolean
	 */
	public function permissions_check( WP_REST_Request $request ) {
		return current_user_can( 'manage_options' );
	}


	/**
	 * Get the advertisers assigned to the authorized Pinterest account.
	 *
	 * @return array
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @throws \Exception PHP Exception.
	 */
	public function get_feed_issues( WP_REST_Request $request ) {

		try {

			$workflow        = false;
			$issues_file_url = $request->has_param( 'feed_issues_url' ) ? $request->get_param( 'feed_issues_url' ) : false;

			if ( false === $issues_file_url ) {
				$workflow = self::get_last_feed_workflow();

				if ( $workflow && isset( $workflow->s3_validation_url ) ) {
					$issues_file_url = $workflow->s3_validation_url;
				}
			}

			if ( empty( $issues_file_url ) ) {
				return array( 'lines' => array() );
			}

			// Get file.
			$issues_file = $this->get_remote_file( $issues_file_url, $workflow );

			if ( empty( $issues_file ) ) {
				throw new \Exception( esc_html__( 'Error downloading Feed Issues file from Pinterest.', 'pinterest-for-woocommerce' ), 400 );
			}

			$lines = self::parse_lines( $issues_file, 0, 10 ); // TODO: pagination?

			if ( ! empty( $lines ) ) {
				$lines = array_map( array( __CLASS__, 'add_product_data' ), $lines );
			}

			return array( 'lines' => $lines );

		} catch ( \Throwable $th ) {

			/* Translators: The error description as returned from the API */
			$error_message = sprintf( esc_html__( 'Could not get current feed\'s issues. [%s]', 'pinterest-for-woocommerce' ), $th->getMessage() );

			return new \WP_Error( \PINTEREST_FOR_WOOCOMMERCE_PREFIX . '_advertisers_error', $error_message, array( 'status' => $th->getCode() ) );
		}
	}


	/**
	 * Add product specific data to each line.
	 *
	 * @param array $line The array contaning each col value for the line.
	 *
	 * @return array
	 */
	private static function add_product_data( $line ) {

		$product = wc_get_product( $line['ItemId'] );

		return array(
			'status'            => 'ERROR' === $line['Code'] ? 'error' : 'warning',
			'product_name'      => $product ? $product->get_name() : esc_html( 'Invalid product', 'pinterest-for-woocommerce' ),
			'product_edit_link' => $product ? get_edit_post_link( $product->get_id() ) : '',
			'issue_description' => $line['Message'],
		);
	}



	/**
	 * Reads the file given in $issues_file, parses and returns the content of lines
	 * from $start_line to $end_line as array items.
	 *
	 * @param string  $issues_file The file path to read from.
	 * @param int     $start_line  The first line to return.
	 * @param int     $end_line    The last line to return.
	 * @param boolean $has_keys    Whether or not the 1st line of the file holds the header keys.
	 *
	 * @return array
	 */
	private static function parse_lines( $issues_file, $start_line, $end_line, $has_keys = true ) {

		$lines      = array();
		$keys       = '';
		$delim      = "\t";
		$start_line = $has_keys ? $start_line + 1 : $start_line;
		$end_line   = $has_keys ? $end_line + 1 : $end_line;

		try {
			$spl = new \SplFileObject( $issues_file );

			if ( $has_keys ) {
				$spl->seek( 0 );
				$keys = $spl->current();
			}

			for ( $i = $start_line; $i <= $end_line; $i++ ) {
				$spl->seek( $i );
				$lines[] = $spl->current();
			}
		} catch ( \Throwable $th ) {

			// Fallback method.
			global $wp_filesystem;

			require_once ABSPATH . '/wp-admin/includes/file.php';

			if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
				$creds = request_filesystem_credentials( site_url() );
				WP_Filesystem( $creds );
			}

			$all_lines = $wp_filesystem->get_contents_array( $issues_file );

			if ( $has_keys ) {
				$keys = $all_lines[0];
			}

			$lines = array_slice( $all_lines, $start_line, ( $end_line - $start_line ) );
		}

		if ( ! empty( $keys ) ) {
			$keys = array_map( 'trim', explode( $delim, $keys ) );
		}

		foreach ( $lines as &$line ) {
			$line = array_combine( $keys, array_map( 'trim', explode( $delim, $line ) ) );
		}

		return $lines;
	}


	/**
	 * Get the file from $url and save it to a temporary location.
	 * Return the path of the temporary file.
	 *
	 * @param string $url       The URL to fetch the file from.
	 * @param mixed  $cache_key The variables to use in order to populate the cache key.
	 *
	 * @return string|boolean
	 */
	private function get_remote_file( $url, $cache_key ) {

		if ( is_array( $cache_key ) && ! empty( $cache_key ) ) {
			$ignore_for_cache = array( 's3_source_url', 's3_validation_url' ); // These 2 are different on every response.
			$cache_key        = array_diff_key( $cache_key, array_flip( $ignore_for_cache ) );
		}

		$cache_key = PINTEREST_FOR_WOOCOMMERCE_PREFIX . '_feed_file_' . md5( $cache_key ? wp_json_encode( $cache_key ) : $url );

		if ( isset( $this->feed_data_files[ $cache_key ] ) && file_exists( $this->feed_data_files[ $cache_key ] ) ) {
			return $this->feed_data_files[ $cache_key ];
		} elseif ( ! empty( $this->feed_data_files ) ) {

			// Cleanup previously stored files.
			foreach ( $this->feed_data_files as $key => $file ) {
				@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged --- We don't care if the file is already gone.
				unset( $this->feed_data_files[ $key ] );
			}
		}

		if ( ! function_exists( 'wp_tempnam' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$target_file = wp_tempnam();

		$response = wp_remote_get(
			$url,
			array(
				'stream'   => true,
				'filename' => $target_file,
				'timeout'  => 300,
			)
		);

		$result = $response && ! is_wp_error( $response ) ? $target_file : false;

		if ( $result ) {
			// Save to cache.
			$this->feed_data_files[ $cache_key ] = $result;
			$this->save_feed_data_cache();
		}

		return $result;
	}

	/**
	 * Save the current contents of feed_data_files to the options table.
	 *
	 * @return void
	 */
	private function save_feed_data_cache() {
		Pinterest_For_Woocommerce()::save_setting( 'feed_data_cache', $this->feed_data_files );
	}

	/**
	 * Get the latest Workflow of the
	 * active feed, for the Merchant saved in the settings.
	 *
	 * @return object
	 *
	 * @throws \Exception PHP Exception.
	 */
	private static function get_last_feed_workflow() {

		$merchant_id = Pinterest_For_Woocommerce()::get_setting( 'merchant_id' );
		$feed_report = Base::get_feed_report( $merchant_id );

		if ( 'success' !== $feed_report['status'] ) {
			throw new \Exception( esc_html__( 'Could not get feed report from Pinterest.', 'pinterest-for-woocommerce' ), 400 );
		}

		if ( ! property_exists( $feed_report['data'], 'workflows' ) || ! is_array( $feed_report['data']->workflows ) || empty( $feed_report['data']->workflows ) ) {
			return false;
		}

		// Get latest workflow.
		usort(
			$feed_report['data']->workflows,
			function ( $a, $b ) {
				return $b->created_at - $a->created_at;
			}
		);

		$workflow = reset( $feed_report['data']->workflows );

		return $workflow;
	}
}
