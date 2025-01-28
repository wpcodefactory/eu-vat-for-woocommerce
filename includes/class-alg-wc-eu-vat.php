<?php
/**
 * EU VAT for WooCommerce - Main Class
 *
 * @version 4.2.2
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT' ) ) :

final class Alg_WC_EU_VAT {

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $version = ALG_WC_EU_VAT_VERSION;

	/**
	 * Core object.
	 *
	 * @since 2.12.12
	 * @var   Alg_WC_EU_VAT_Core class instance
	 */
	public $core = null;

	/**
	 * Admin settings.
	 *
	 * @since 2.12.12
	 * @var   array
	 */
	public $settings = array();

	/**
	 * Instance.
	 *
	 * @since 1.0.0
	 * @var   Alg_WC_EU_VAT The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_EU_VAT Instance.
	 *
	 * Ensures only one instance of Alg_WC_EU_VAT is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @static
	 * @return  Alg_WC_EU_VAT - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_EU_VAT Constructor.
	 *
	 * @version 4.2.2
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
			require_once plugin_dir_path( ALG_WC_EU_VAT_FILE ) . 'vendor/autoload.php';
		}

		// For debug
		require_once plugin_dir_path( __FILE__ ) . 'functions/alg-wc-eu-vat-functions-debug.php';

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		if ( ! did_action( 'get_header' ) ){
			// Define standard session type for admin
			define( 'ALG_WC_EU_VAT_SESSION_TYPE', 'standard' );
		}

		// Pro
		if ( 'eu-vat-for-woocommerce-pro.php' === basename( ALG_WC_EU_VAT_FILE ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'pro/class-alg-wc-eu-vat-pro.php';
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * localize.
	 *
	 * @version 4.0.0
	 * @since   3.0.0
	 */
	function localize() {
		load_plugin_textdomain(
			'eu-vat-for-woocommerce',
			false,
			dirname( plugin_basename( ALG_WC_EU_VAT_FILE ) ) . '/langs/'
		);
	}

	/**
	 * wc_declare_compatibility.
	 *
	 * @version 4.2.2
	 * @since   2.9.12
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	 */
	function wc_declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			$files = (
				defined( 'ALG_WC_EU_VAT_FILE_FREE' ) ?
				array( ALG_WC_EU_VAT_FILE, ALG_WC_EU_VAT_FILE_FREE ) :
				array( ALG_WC_EU_VAT_FILE )
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
	 * @version 4.0.0
	 * @since   1.0.0
	 */
	function includes() {

		// Functions
		require_once plugin_dir_path( __FILE__ ) . 'functions/alg-wc-eu-vat-functions-general.php';

		// Core
		$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-eu-vat-core.php';

	}

	/**
	 * admin.
	 *
	 * @version 4.0.0
	 * @since   1.2.0
	 */
	function admin() {

		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( ALG_WC_EU_VAT_FILE ), array( $this, 'action_links' ) );

		// "Recommendations" page
		$this->add_cross_selling_library();

		// WC Settings tab as WPFactory submenu item
		$this->move_wc_settings_tab_to_wpfactory_menu();

		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-eu-vat-settings-section.php';
		$this->settings = array();
		$this->settings['general']    = require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-eu-vat-settings-general.php';
		$this->settings['validation'] = require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-eu-vat-settings-validation.php';
		$this->settings['admin']      = require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-eu-vat-settings-admin.php';

		// Rates tool
		require_once plugin_dir_path( __FILE__ ) . 'admin/class-alg-wc-eu-vat-country-rates.php';

		// Version update
		if ( get_option( 'alg_wc_eu_vat_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}

	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 4.0.0
	 * @since   1.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_eu_vat' ) . '">' .
			__( 'Settings', 'eu-vat-for-woocommerce' ) .
		'</a>';

		if ( 'eu-vat-for-woocommerce.php' === basename( ALG_WC_EU_VAT_FILE ) ) {
			$custom_links[] = '<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' .
				__( 'Unlock All', 'eu-vat-for-woocommerce' ) .
			'</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 4.0.0
	 * @since   3.1.0
	 */
	function add_cross_selling_library() {

		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => ALG_WC_EU_VAT_FILE ) );
		$cross_selling->init();

	}

	/**
	 * move_wc_settings_tab_to_wpfactory_menu.
	 *
	 * @version 3.1.0
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
			'wc_settings_tab_id' => 'alg_wc_eu_vat',
			'menu_title'         => __( 'EU VAT', 'eu-vat-for-woocommerce' ),
			'page_title'         => __( 'EU VAT', 'eu-vat-for-woocommerce' ),
		) );

	}

	/**
	 * Add EU VAT settings tab to WooCommerce settings.
	 *
	 * @version 4.0.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-eu-vat-settings.php';
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
	 * @version 4.0.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( ALG_WC_EU_VAT_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 4.0.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( ALG_WC_EU_VAT_FILE ) );
	}

}

endif;
