<?php
/**
 * Installation related functions and actions.
 *
 * @author   WooCommece
 * @category Core
 * @package  Pinterest_For_Woocommerce
 * @version  1.0.0
 */

use Automattic\WooCommerce\Pinterest as Pinterest;

if ( ! class_exists( 'Pinterest_For_Woocommerce' ) ) :

	final class Pinterest_For_Woocommerce {

		/**
		 * Pinterest_For_Woocommerce version.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * The single instance of the class.
		 *
		 * @var Pinterest_For_Woocommerce
		 * @since 1.0.0
		 */
		protected static $instance = null;

		protected static $initialized = false;

		/**
		 * Main Pinterest_For_Woocommerce Instance.
		 *
		 * Ensures only one instance of Pinterest_For_Woocommerce is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see Pinterest_For_Woocommerce()
		 * @return Pinterest_For_Woocommerce - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->initalize_plugin();
			}
			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 * @since 1.0.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'pinterest-for-woocommerce' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'pinterest-for-woocommerce' ), '1.0.0' );
		}

		/**
		 * Pinterest_For_Woocommerce Initializer.
		 */
		public function initalize_plugin() {
			if ( self::$initialized ) {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'Only a single instance of this class is allowed. Use singleton.', 'pinterest-for-woocommerce' ), '1.0.0' );
				return;
			}

			self::$initialized = true;

			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'pinterest_for_woocommerce_loaded' );
		}

		/**
		 * Define Pinterest_For_Woocommerce Constants.
		 */
		private function define_constants() {
			define( 'PINTEREST_FOR_WOOCOMMERCE_PREFIX', 'pinterest-for-woocommerce' );
			define( 'PINTEREST_FOR_WOOCOMMERCE_PLUGIN_BASENAME', plugin_basename( PINTEREST_FOR_WOOCOMMERCE_PLUGIN_FILE ) );
			define( 'PINTEREST_FOR_WOOCOMMERCE_VERSION', $this->version );
			define( 'PINTEREST_FOR_WOOCOMMERCE_OPTION_NAME', 'pinterest_for_woocommerce' );
			define( 'PINTEREST_FOR_WOOCOMMERCE_LOG_PREFIX', 'pinterest-for-woocommerce' );
			define( 'PINTEREST_FOR_WOOCOMMERCE_SETUP_GUIDE', PINTEREST_FOR_WOOCOMMERCE_PREFIX . '-setup-guide-app' );
			define( 'PINTEREST_FOR_WOOCOMMERCE_WOO_CONNECT_URL', 'https://connect.woocommerce.com/' );
			define( 'PINTEREST_FOR_WOOCOMMERCE_API_NAMESPACE', 'pinterest' );
			define( 'PINTEREST_FOR_WOOCOMMERCE_API_VERSION', '1' );
			define( 'PINTEREST_FOR_WOOCOMMERCE_API_AUTH_ENDPOINT', 'oauth/callback' );
			define( 'PINTEREST_FOR_WOOCOMMERCE_AUTH', PINTEREST_FOR_WOOCOMMERCE_PREFIX . '_auth_key' );
		}


		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {
			include_once 'includes/pinterest-for-woocommerce-core-functions.php';
			include_once 'includes/class-pinterest-for-woocommerce-install.php';

			Pinterest\API\Base::instance();

			if ( $this->is_request( 'admin' ) ) {
				include_once 'includes/admin/class-pinterest-for-woocommerce-admin.php';
			}

			if ( $this->is_request( 'frontend' ) ) {
				include_once 'includes/class-pinterest-for-woocommerce-frontend-assets.php';
			}
		}

		/**
		 * Hook into actions and filters.
		 * @since  1.0.0
		 */
		private function init_hooks() {
			add_action( 'init', array( $this, 'init' ), 0 );
			add_action( 'activated_plugin', array( $this, 'maybe_redirect_setup_guide' ) );
			add_action( 'rest_api_init', array( $this, 'init_api_endpoints' ) );
			add_action( 'wp_head', array( $this, 'inject_verification_code' ) );

			add_action( 'pinterest_for_woocommerce_account_updated', array( $this, 'update_account_data' ) );
		}

		/**
		 * Init Pinterest_For_Woocommerce when WordPress Initialises.
		 */
		public function init() {
			// Before init action.
			do_action( 'before_pinterest_for_woocommerce_init' );

			// Set up localisation.
			$this->load_plugin_textdomain();

			// Init action.
			do_action( 'pinterest_for_woocommerce_init' );

		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/pinterest-for-woocommerce/pinterest-for-woocommerce-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/pinterest-for-woocommerce-LOCALE.mo
		 */
		private function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'pinterest-for-woocommerce' );

			load_textdomain( 'pinterest-for-woocommerce', WP_LANG_DIR . '/pinterest-for-woocommerce/pinterest-for-woocommerce-' . $locale . '.mo' );
			load_plugin_textdomain( 'pinterest-for-woocommerce', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Get the template path.
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'pinterest_for_woocommerce_template_path', 'pinterest-for-woocommerce/' );
		}

		/**
		 * Get Ajax URL.
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}



		/**
		 * Return APP Settings
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $force
		 * @return array
		 */
		public static function get_settings( $force = false ) {

			static $settings;

			if ( is_null( $settings ) || $force ) {
				$settings = get_option( PINTEREST_FOR_WOOCOMMERCE_OPTION_NAME );
			}

			return $settings;
		}


		/**
		 * Return APP Setting based on its key
		 *
		 * @since 1.0.0
		 *
		 * @param string $key
		 * @param boolean $force
		 *
		 * @return mixed
		 */
		public static function get_setting( $key, $force = false ) {

			$settings = self::get_settings( $force );

			return empty( $settings[ $key ] ) ? false : $settings[ $key ];
		}


		/**
		 * Save APP Setting
		 *
		 * @since 1.0.0
		 *
		 * @param string $key
		 * @param mixed $data
		 *
		 * @return boolean
		 */
		public static function save_setting( $key, $data ) {

			$settings = self::get_settings( true );

			$settings[ $key ] = $data;

			return self::save_settings( $settings );
		}


		/**
		 * Save APP Settings
		 *
		 * @since 1.0.0
		 *
		 * @param array $settings
		 *
		 * @return boolean
		 */
		public static function save_settings( $settings ) {
			return update_option( PINTEREST_FOR_WOOCOMMERCE_OPTION_NAME, $settings );
		}

		/**
		 * Add API endpoints
		 *
		 * @since 1.0.0
		 */
		public function init_api_endpoints() {
			new Pinterest\API\Auth();
			new Pinterest\API\DomainVerification();
		}

		/**
		 * Get decripted token data
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function get_token() {

			$token = self::get_setting( 'token', true );

			try {
				$token['access_token'] = empty( $token['access_token'] ) ? '' : Pinterest\Crypto::decrypt( $token['access_token'] );
			} catch ( \Exception $e ) {
				$token = array();
			}

			return $token;
		}


		/**
		 * Save encripted token data
		 *
		 * @since 1.0.0
		 *
		 * @param array $token
		 *
		 * @return boolean
		 */
		public static function save_token( $token ) {

			$settings = self::get_settings();

			$token['access_token'] = empty( $token['access_token'] ) ? '' : Pinterest\Crypto::encrypt( $token['access_token'] );

			$settings['token'] = $token;

			return self::save_settings( $settings );
		}


		/**
		 * Clear the token
		 *
		 * @since 1.0.0
		 *
		 * @return boolean
		 */
		public static function clear_token() {
			return self::save_token( array() );
		}


		/**
		 * Return WooConnect Bridge URL
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public static function get_connection_proxy_url() {

			/**
			 * Filters the proxy URL.
			 *
			 * @since 1.0.0
			 *
			 * @param string $proxy_url the connection proxy URL
			 */
			return (string) trailingslashit( apply_filters( 'pinterest_for_woocommerce_connection_proxy_url', PINTEREST_FOR_WOOCOMMERCE_WOO_CONNECT_URL ) );
		}


		/**
		 * Return Service Login URL
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public static function get_service_login_url() {

			$control_key = uniqid();
			$state       = http_build_query(
				array(
					'redirect' => get_rest_url( null, PINTEREST_FOR_WOOCOMMERCE_API_NAMESPACE . '/v' . PINTEREST_FOR_WOOCOMMERCE_API_VERSION . '/' . PINTEREST_FOR_WOOCOMMERCE_API_AUTH_ENDPOINT ) . '?control=' . $control_key . '&view=' . ( isset( $_GET['view'] ) && 'wizard' === $_GET['view'] ?: 'settings' ),
				)
			);

			set_transient( PINTEREST_FOR_WOOCOMMERCE_AUTH, $control_key, MINUTE_IN_SECONDS * 5 );

			return self::get_connection_proxy_url() . 'login/pinterestv3?' . $state;
		}


		/**
		 * If Setup Guide is not complete, redirects to Settings page
		 *
		 * @since 1.0.0
		 */
		public function maybe_redirect_setup_guide( $plugin ) {

			return;

			if ( PINTEREST_FOR_WOOCOMMERCE_PLUGIN_BASENAME !== $plugin ) {
				return;
			}

			$settings = self::get_settings( true );

			if ( 'no' === $settings['is_setup_complete'] ) {
				$setup_guide_url = add_query_arg(
					array(
						'page' => PINTEREST_FOR_WOOCOMMERCE_SETUP_GUIDE,
					),
					get_admin_url( null, 'admin.php' )
				);
				wp_safe_redirect( $setup_guide_url );
				exit;
			}
		}


		public function inject_verification_code() {

			if ( self::get_setting( 'verfication_code' ) ) {
				printf( '<meta name="p:domain_verify" content="%s"/>', esc_attr( self::get_setting( 'verfication_code' ) ) );
			}
		}

		public function update_account_data() {

			$account_data = Pinterest\API\Base::get_account_info();

			if ( 'success' === $account_data['status'] ) {

				$data = array_intersect_key(
					(array) $account_data['data'],
					array(
						'verified_domains' => '',
						'domain_verified'  => '',
						'username'         => '',
						'id'               => '',
						'image_medium_url' => '',
					)
				);

				Pinterest_For_Woocommerce()::save_setting( 'account_data', $data );
			}

		}


		public static function is_connected() {

			$token = self::get_setting( 'token' );

			return (bool) ! empty( $token['access_token'] );

		}

		public static function is_domain_verified() {

			$account_data = self::get_setting( 'account_data' );
			$domain       = wp_parse_url( site_url(), PHP_URL_HOST );

			return (bool) ! empty( $account_data ) && isset( $account_data['verified_domains'][ $domain ] );
		}
	}

endif;
