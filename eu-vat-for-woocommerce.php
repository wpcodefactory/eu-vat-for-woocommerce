<?php
/*
Plugin Name: EU/UK VAT Validation Manager for WooCommerce
Plugin URI: https://wpfactory.com/item/eu-vat-for-woocommerce/
Description: Manage EU VAT in WooCommerce. Beautifully.
Version: 4.7.0
Author: WPFactory
Author URI: https://wpfactory.com/
Text Domain: eu-vat-for-woocommerce
Domain Path: /langs
WC tested up to: 10.9
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( 'eu-vat-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 4.7.0
	 * @since   3.2.0
	 */
	$plugin = 'eu-vat-for-woocommerce-pro/eu-vat-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		(
			is_multisite() &&
			array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) )
		)
	) {
		defined( 'WPFACTORY_WC_EU_VAT_FILE_FREE' ) || define( 'WPFACTORY_WC_EU_VAT_FILE_FREE', __FILE__ );
		return;
	}
}

defined( 'WPFACTORY_WC_EU_VAT_VERSION' ) || define( 'WPFACTORY_WC_EU_VAT_VERSION', '4.7.0' );

defined( 'WPFACTORY_WC_EU_VAT_FILE' ) || define( 'WPFACTORY_WC_EU_VAT_FILE', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpfactory-wc-eu-vat.php';

if ( ! function_exists( 'wpfactory_wc_eu_vat' ) ) {
	/**
	 * Returns the main instance of WPFactory_WC_EU_VAT to prevent the need to use globals.
	 *
	 * @version 4.7.0
	 * @since   1.0.0
	 *
	 * @return  WPFactory_WC_EU_VAT
	 */
	function wpfactory_wc_eu_vat() {
		return WPFactory_WC_EU_VAT::instance();
	}
}

/**
 * plugins_loaded.
 *
 * @version 4.7.0
 */
add_action( 'plugins_loaded', 'wpfactory_wc_eu_vat' );

/**
 * Load block.
 *
 * @version 4.7.0
 * @since   3.1.5
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/blocks/wpfactory-wc-eu-vat-blocks-initialize.php';
