<?php
/**
 * EU VAT for WooCommerce - Main Class
 *
 * @version 4.7.0
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_EU_VAT' ) ) :

final class WPFactory_WC_EU_VAT {

	/**
	 * Plugin version.
	 *
	 * @version 4.7.0
	 * @since 1.0.0
	 * @var   string
	 */
	public $version = WPFACTORY_WC_EU_VAT_VERSION;

	/**
	 * Core object.
	 *
	 * @since 2.12.12
	 * @var   WPFactory_WC_EU_VAT_Core class instance
	 */
	public $core = null;

	/**
	 * Instance.
	 *
	 * @since 1.0.0
	 * @var   WPFactory_WC_EU_VAT The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Main WPFactory_WC_EU_VAT Instance.
	 *
	 * Ensures only one instance of WPFactory_WC_EU_VAT is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @static
	 * @return  WPFactory_WC_EU_VAT - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * WPFactory_WC_EU_VAT Constructor.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Check for active WooCommerce plugin
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Load libs
		if ( is_admin() ) {
			require_once plugin_dir_path( WPFACTORY_WC_EU_VAT_FILE ) . 'vendor/autoload.php';
		}

		// For debug
		require_once plugin_dir_path( __FILE__ ) . 'functions/wpfactory-wc-eu-vat-functions-debug.php';

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		// Pro
		if ( 'eu-vat-for-woocommerce-pro.php' === basename( WPFACTORY_WC_EU_VAT_FILE ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'pro/class-wpfactory-wc-eu-vat-pro.php';
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * wc_declare_compatibility.
	 *
	 * @version 4.7.0
	 * @since   2.9.12
	 *
	 * @see     https://developer.woocommerce.com/docs/features/high-performance-order-storage/recipe-book/
	 */
	function wc_declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			$files = (
				defined( 'WPFACTORY_WC_EU_VAT_FILE_FREE' ) ?
				array( WPFACTORY_WC_EU_VAT_FILE, WPFACTORY_WC_EU_VAT_FILE_FREE ) :
				array( WPFACTORY_WC_EU_VAT_FILE )
			);
			foreach ( $files as $file ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
					'custom_order_tables',
					$file,
					true
				);
			}
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function includes() {

		// Functions
		require_once plugin_dir_path( __FILE__ ) . 'functions/wpfactory-wc-eu-vat-functions-general.php';

		// Core
		$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-wpfactory-wc-eu-vat-core.php';

	}

	/**
	 * admin.
	 *
	 * @version 4.7.0
	 * @since   1.2.0
	 */
	function admin() {

		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( WPFACTORY_WC_EU_VAT_FILE ), array( $this, 'action_links' ) );

		// "Recommendations" page
		add_action( 'init', array( $this, 'add_cross_selling_library' ) );

		// WC Settings tab as WPFactory submenu item
		add_action( 'init', array( $this, 'move_wc_settings_tab_to_wpfactory_menu' ) );

		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );

		// Rates tool
		require_once plugin_dir_path( __FILE__ ) . 'admin/class-wpfactory-wc-eu-vat-country-rates.php';

		// Version update
		if ( get_option( 'alg_wc_eu_vat_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}

	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=wpfactory_wc_eu_vat' ) . '">' .
			__( 'Settings', 'eu-vat-for-woocommerce' ) .
		'</a>';

		if ( 'eu-vat-for-woocommerce.php' === basename( WPFACTORY_WC_EU_VAT_FILE ) ) {
			$custom_links[] = '<a target="_blank" style="font-weight: bold; color: green;" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' .
				__( 'Go Pro', 'eu-vat-for-woocommerce' ) .
			'</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 4.7.0
	 * @since   3.1.0
	 */
	function add_cross_selling_library() {

		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => WPFACTORY_WC_EU_VAT_FILE ) );
		$cross_selling->init();

	}

	/**
	 * move_wc_settings_tab_to_wpfactory_menu.
	 *
	 * @version 4.7.0
	 * @since   3.1.0
	 */
	function move_wc_settings_tab_to_wpfactory_menu() {

		if ( ! class_exists( '\WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu' ) ) {
			return;
		}

		$wpfactory_admin_menu = \WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu::get_instance();

		if ( ! method_exists( $wpfactory_admin_menu, 'move_wc_settings_tab_to_wpfactory_menu' ) ) {
			return;
		}

		$wpfactory_admin_menu->move_wc_settings_tab_to_wpfactory_menu( array(
			'wc_settings_tab_id' => 'wpfactory_wc_eu_vat',
			'menu_title'         => __( 'EU VAT', 'eu-vat-for-woocommerce' ),
			'page_title'         => __( 'EU/UK VAT Validation Manager', 'eu-vat-for-woocommerce' ),
			'plugin_icon'        => array(
				'url' => 'https://ps.w.org/eu-vat-for-woocommerce/assets/icon.svg?rev=2969433',
			),
		) );

	}

	/**
	 * Add EU VAT settings tab to WooCommerce settings.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once plugin_dir_path( __FILE__ ) . 'settings/class-wpfactory-wc-eu-vat-settings.php';
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function version_updated() {
		update_option( 'alg_wc_eu_vat_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( WPFACTORY_WC_EU_VAT_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( WPFACTORY_WC_EU_VAT_FILE ) );
	}

	/**
	 * Get the plugin asset URL.
	 *
	 * @version 4.6.4
	 * @since   4.6.4
	 *
	 * @return  string
	 */
	function plugin_asset_url( $file ) {

		$dir = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '/assets/' : '/assets/build/';

		return $this->plugin_url() . $dir . ltrim( $file, '/' );
	}

}

endif;
