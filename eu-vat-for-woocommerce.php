<?php
/*
Plugin Name: EU VAT for WooCommerce
Plugin URI: https://wpfactory.com/item/eu-vat-for-woocommerce/
Description: Manage EU VAT in WooCommerce. Beautifully.
Version: 1.8.0
Author: WPWhale
Author URI: https://wpfactory.com/author/wpwhale/
Text Domain: eu-vat-for-woocommerce
Domain Path: /langs
Copyright: © 2019 WPWhale
WC tested up to: 3.8
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_EU_VAT' ) ) :

/**
 * Main Alg_WC_EU_VAT Class
 *
 * @class   Alg_WC_EU_VAT
 * @version 1.7.2
 * @since   1.0.0
 */
final class Alg_WC_EU_VAT {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '1.8.0';

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
	 * @version 1.7.1
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Check for active plugins
		if (
			! $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ||
			( 'eu-vat-for-woocommerce.php' === basename( __FILE__ ) && $this->is_plugin_active( 'eu-vat-for-woocommerce-pro/eu-vat-for-woocommerce-pro.php' ) )
		) {
			return;
		}

		// For debug
		require_once( 'includes/functions/alg-wc-eu-vat-functions-debug.php' );

		// Set up localisation
		load_plugin_textdomain( 'eu-vat-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Pro
		if ( 'eu-vat-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once( 'includes/pro/class-alg-wc-eu-vat-pro.php' );
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
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
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 */
	function includes() {
		// Functions
		require_once( 'includes/functions/alg-wc-eu-vat-functions-general.php' );
		// Core
		$this->core = require_once( 'includes/class-alg-wc-eu-vat-core.php' );
	}

	/**
	 * admin.
	 *
	 * @version 1.5.0
	 * @since   1.2.0
	 */
	function admin() {
		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once( 'includes/settings/class-alg-wc-eu-vat-settings-section.php' );
		$this->settings = array();
		$this->settings['general']    = require_once( 'includes/settings/class-alg-wc-eu-vat-settings-general.php' );
		$this->settings['validation'] = require_once( 'includes/settings/class-alg-wc-eu-vat-settings-validation.php' );
		$this->settings['admin']      = require_once( 'includes/settings/class-alg-wc-eu-vat-settings-admin.php' );
		// Rates tool
		require_once( 'includes/admin/class-alg-wc-eu-vat-country-rates.php' );
		// Version update
		if ( get_option( 'alg_wc_eu_vat_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_eu_vat' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'eu-vat-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a target="_blank" href="https://wpfactory.com/item/eu-vat-for-woocommerce/">' .
				__( 'Unlock All', 'eu-vat-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Add EU VAT settings tab to WooCommerce settings.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-alg-wc-eu-vat-settings.php' );
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
	 * @return  Alg_WC_EU_VAT
	 */
	function alg_wc_eu_vat() {
		return Alg_WC_EU_VAT::instance();
	}
}

alg_wc_eu_vat();
