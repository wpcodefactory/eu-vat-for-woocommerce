<?php
/*
Plugin Name: EU/UK VAT Validation Manager for WooCommerce
Plugin URI: https://wpfactory.com/item/eu-vat-for-woocommerce/
Description: Manage EU VAT in WooCommerce. Beautifully.
Version: 3.1.5
Author: WPFactory
Author URI: https://wpfactory.com/
Text Domain: eu-vat-for-woocommerce
Domain Path: /langs
WC tested up to: 9.4
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EU_VAT' ) ) :

/**
 * Main Alg_WC_EU_VAT Class
 *
 * @class   Alg_WC_EU_VAT
 * @version 3.1.1
 * @since   1.0.0
 */
final class Alg_WC_EU_VAT {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '3.1.5';

	/**
	 * core object.
	 *
	 * @var   Alg_WC_EU_VAT_Core class instance
	 * @since 2.12.12
	 */

	public $core = null;

	/**
	 * admin settings.
	 *
	 * @var   array
	 * @since 2.12.12
	 */
	public $settings = array();

	/**
	 * @var   Alg_WC_EU_VAT The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_EU_VAT Instance
	 *
	 * Ensures only one instance of Alg_WC_EU_VAT is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
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
	 * @version 3.1.1
	 * @since   1.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		// Check for active plugins
		if (
			! $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ||
			(
				'eu-vat-for-woocommerce.php' === basename( __FILE__ ) &&
				$this->is_plugin_active( 'eu-vat-for-woocommerce-pro/eu-vat-for-woocommerce-pro.php' )
			)
		) {
			return;
		}

		// Load libs
		if ( is_admin() ) {
			require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
		}

		// For debug
		require_once plugin_dir_path( __FILE__ ) . 'includes/functions/alg-wc-eu-vat-functions-debug.php';

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Pro
		if ( 'eu-vat-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/pro/class-alg-wc-eu-vat-pro.php';
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
	 * @version 3.1.1
	 * @since   2.9.12
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	 */
	function wc_declare_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				dirname( __FILE__ ),
				true
			);
		}
	}

	/**
	 * is_plugin_active.
	 *
	 * @version 1.7.1
	 * @since   1.7.1
	 */
	function is_plugin_active( $plugin ) {
		return ( function_exists( 'is_plugin_active' ) ? is_plugin_active( $plugin ) :
			(
				in_array( $plugin, apply_filters( 'active_plugins', ( array ) get_option( 'active_plugins', array() ) ) ) ||
				( is_multisite() && array_key_exists( $plugin, ( array ) get_site_option( 'active_sitewide_plugins', array() ) ) )
			)
		);
	}

	/**
	 * localize.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function localize() {
		load_plugin_textdomain(
			'eu-vat-for-woocommerce',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/langs/'
		);
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 3.1.0
	 * @since   1.0.0
	 */
	function includes() {

		// Functions
		require_once plugin_dir_path( __FILE__ ) . 'includes/functions/alg-wc-eu-vat-functions-general.php';

		// Core
		$this->core = require_once plugin_dir_path( __FILE__ ) . 'includes/class-alg-wc-eu-vat-core.php';

	}

	/**
	 * admin.
	 *
	 * @version 3.1.0
	 * @since   1.2.0
	 */
	function admin() {

		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

		// "Recommendations" page
		$this->add_cross_selling_library();

		// WC Settings tab as WPFactory submenu item
		$this->move_wc_settings_tab_to_wpfactory_menu();

		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-alg-wc-eu-vat-settings-section.php';
		$this->settings = array();
		$this->settings['general']    = require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-alg-wc-eu-vat-settings-general.php';
		$this->settings['validation'] = require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-alg-wc-eu-vat-settings-validation.php';
		$this->settings['admin']      = require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-alg-wc-eu-vat-settings-admin.php';

		// Rates tool
		require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-alg-wc-eu-vat-country-rates.php';

		// Version update
		if ( get_option( 'alg_wc_eu_vat_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}

	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 3.1.0
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
		if ( 'eu-vat-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' .
				__( 'Unlock All', 'eu-vat-for-woocommerce' ) .
			'</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function add_cross_selling_library() {

		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => __FILE__ ) );
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
	 * @version 3.1.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-alg-wc-eu-vat-settings.php';
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
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_eu_vat' ) ) {
	/**
	 * Returns the main instance of Alg_WC_EU_VAT to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @return  Alg_WC_EU_VAT
	 */
	function alg_wc_eu_vat() {
		return Alg_WC_EU_VAT::instance();
	}
}

/**
 * plugins_loaded.
 *
 * @version 3.1.1
 */
add_action( 'plugins_loaded', 'alg_wc_eu_vat' );

/**
 * Load block.
 *
 * @version 3.1.5
 * @since   3.1.5
 */
require_once plugin_dir_path( __FILE__ ) . 'eu-vat-for-woocommerce-blocks-initialize.php';
